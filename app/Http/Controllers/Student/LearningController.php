<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AssessmentAttempt;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Level;
use App\Models\Track;
use App\Services\AssessmentGradingService;
use App\Services\LevelAccessService;
use App\Services\LevelSequenceService;
use App\Services\RegionPricingService;
use App\Services\TrackProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LearningController extends Controller
{
    public function level(Track $track, Level $level, LevelAccessService $levelAccess, LevelSequenceService $sequence): View|RedirectResponse
    {
        $this->authorize('learn', [$track, $this->currentRegion()]);

        $enrollment = $this->enrollmentFor($track);

        if (! $levelAccess->canAccess($enrollment, $level)) {
            return $this->redirectToCheckpoint($track, $level, $levelAccess);
        }

        $level->load(['sections.lessons.video', 'sections.lessons.resources.media', 'sections.lessons.activity']);

        // The page shows every level of the track in one scroll, so every
        // level's sections/lessons/activity are loaded up front rather than
        // one at a time, to avoid N+1 queries while rendering the list.
        $allLevels = $track->levels()->with(['sections.lessons.video', 'sections.lessons.resources.media', 'sections.lessons.activity'])->get();

        $completedLessonIds = LessonProgress::query()
            ->where('enrollment_id', $enrollment->id)
            ->where('status', 'completed')
            ->pluck('lesson_id');

        $checkedAssessmentIds = $this->checkedAssessmentIds(Auth::user());

        $nodes = $sequence->nodes($level);

        $resumeNode = $nodes->first(fn ($node) => $node->type === 'lesson'
            ? ! $completedLessonIds->contains($node->lesson->id)
            : ! $checkedAssessmentIds->contains($node->assessment->id));

        return view('student.level', [
            'track' => $track,
            'level' => $level,
            'allLevels' => $allLevels,
            'completedLessonIds' => $completedLessonIds,
            'checkedAssessmentIds' => $checkedAssessmentIds,
            'levelStatuses' => $levelAccess->statusesFor($enrollment, $track),
            'resumeNode' => $resumeNode,
            // Only a student who has actually opened a lesson before is
            // "resuming" — otherwise the highlighted next step is their
            // very first one, so it should say "Start", not "Resume".
            'hasStartedTrack' => $enrollment->last_viewed_lesson_id !== null,
            'resumeStep' => $this->stepLink($track, $level, $resumeNode, 'Resume'),
            'stats' => $this->trackStats($allLevels, $completedLessonIds, $checkedAssessmentIds),
            'nextTrack' => $this->nextTrackAfter($track),
        ]);
    }

    /**
     * Header stats for the course: total video count, remaining watch time
     * across every incomplete lesson in the track, and how many Activity
     * Questions have been submitted track-wide.
     *
     * @param  \Illuminate\Support\Collection<int, Level>  $allLevels
     * @return array{videoCount: int, timeLeft: string, examsDone: int, examsTotal: int}
     */
    private function trackStats($allLevels, $completedLessonIds, $checkedAssessmentIds): array
    {
        $lessons = $allLevels->flatMap->sections->flatMap->lessons;

        $videoCount = $lessons->filter(fn ($lesson) => $lesson->video_media_id !== null
            || $lesson->resources->contains(fn ($r) => $r->media?->type === 'video')
        )->count();

        $remainingSeconds = $lessons
            ->reject(fn ($lesson) => $completedLessonIds->contains($lesson->id))
            ->sum('duration_seconds');

        $assessments = $allLevels->flatMap->sections->flatMap->lessons->pluck('activity')->filter();

        return [
            'videoCount' => $videoCount,
            'timeLeft' => $this->formatDuration((int) $remainingSeconds),
            'examsDone' => $assessments->filter(fn ($assessment) => $checkedAssessmentIds->contains($assessment->id))->count(),
            'examsTotal' => $assessments->count(),
        ];
    }

    private function formatDuration(int $seconds): string
    {
        if ($seconds <= 0) {
            return '0m';
        }

        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        return trim(($hours > 0 ? "{$hours}h " : '')."{$minutes}m");
    }

    public function resume(Track $track, Level $level, LevelAccessService $levelAccess): RedirectResponse
    {
        $this->authorize('learn', [$track, $this->currentRegion()]);

        $enrollment = $this->enrollmentFor($track);

        if (! $levelAccess->canAccess($enrollment, $level)) {
            return $this->redirectToCheckpoint($track, $level, $levelAccess);
        }

        $lesson = $level->lessons()->first();

        abort_if(! $lesson, 404);

        return redirect()->route('student.tracks.learn', [$track, $level, $lesson]);
    }

    public function lesson(Track $track, Level $level, Lesson $lesson, LevelAccessService $levelAccess, LevelSequenceService $sequence): View|RedirectResponse
    {
        $this->authorize('learn', [$track, $this->currentRegion()]);
        $this->authorize('view', $lesson);
        $this->ensureLessonBelongsToLevel($level, $lesson);

        $user = Auth::user();
        $enrollment = $this->enrollmentFor($track);

        if (! $levelAccess->canAccess($enrollment, $level)) {
            return $this->redirectToCheckpoint($track, $level, $levelAccess);
        }

        $lesson->load(['video', 'ebook.file', 'resources.media', 'section', 'activity.questions.options']);

        $nodes = $sequence->nodes($level);
        [$previousNode] = $sequence->neighboursOfLesson($nodes, $lesson);

        // Sequential gating: a student cannot skip past a lesson's Activity
        // Questions by jumping straight to the URL of the next lesson.
        // Activity Questions are now shown inline on their own lesson's
        // page rather than a separate one, so the redirect target is that
        // lesson's page instead of a dedicated activity URL.
        if ($previousNode && $previousNode->type === 'activity' && ! $this->grading()->hasSubmittedAttempt($previousNode->assessment, $user)) {
            return redirect()
                ->route('student.tracks.learn', [$track, $level, $previousNode->lesson])
                ->with('status', 'Complete the Activity Questions before moving on.');
        }

        // The "Next" link skips over this lesson's own activity node in the
        // sequence (it's rendered inline below, not a separate page) and
        // points straight to whatever comes after it.
        $lessonIndex = $nodes->search(fn ($node) => $node->type === 'lesson' && $node->lesson->id === $lesson->id);
        $nextNode = $nodes->get($lessonIndex + 1);
        if ($nextNode && $nextNode->type === 'activity' && $nextNode->lesson->id === $lesson->id) {
            $nextNode = $nodes->get($lessonIndex + 2);
        }

        $completedLessonIds = LessonProgress::query()
            ->where('enrollment_id', $enrollment->id)
            ->where('status', 'completed')
            ->pluck('lesson_id');

        $progress = LessonProgress::query()->firstOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
            ['enrollment_id' => $enrollment->id, 'status' => 'started', 'started_at' => now()]
        );

        $progress->update(['last_viewed_at' => now()]);
        $enrollment->update(['last_viewed_lesson_id' => $lesson->id, 'last_viewed_at' => now()]);

        $isCompleted = $progress->status === 'completed';

        $assessment = $lesson->activity;
        $attempt = null;
        $attemptsUsed = 0;
        if ($assessment && $isCompleted) {
            $attempt = $this->grading()->latestSubmittedAttempt($assessment, $user);
            $attemptsUsed = $this->grading()->attemptsUsed($assessment, $user);
        }

        return view('student.learn', [
            'track' => $track,
            'level' => $level,
            'lesson' => $lesson,
            'completedLessonIds' => $completedLessonIds,
            'previousStep' => $this->stepLink($track, $level, $previousNode, 'Previous'),
            'nextStep' => $this->stepLink($track, $level, $nextNode, 'Next'),
            'isCompleted' => $isCompleted,
            'resumeSeconds' => $progress->video_progress_seconds ?? 0,
            'assessment' => $assessment,
            'attempt' => $attempt,
            'checked' => (bool) $attempt,
            'attemptsUsed' => $attemptsUsed,
            'maxAttempts' => AssessmentGradingService::MAX_ATTEMPTS,
        ]);
    }

    /**
     * The next track a student would move on to after this one, shown as an
     * upsell in the learning page's sidebar.
     */
    private function nextTrackAfter(Track $track): ?Track
    {
        return Track::query()
            ->where('status', 'published')
            ->where('order', '>', $track->order)
            ->orderBy('order')
            ->first();
    }

    public function lessonActivity(Track $track, Level $level, Lesson $lesson, LevelAccessService $levelAccess, LevelSequenceService $sequence): View|RedirectResponse
    {
        $this->authorize('learn', [$track, $this->currentRegion()]);
        $this->ensureLessonBelongsToLevel($level, $lesson);

        $user = Auth::user();
        $enrollment = $this->enrollmentFor($track);

        if (! $levelAccess->canAccess($enrollment, $level)) {
            return $this->redirectToCheckpoint($track, $level, $levelAccess);
        }

        $assessment = $sequence->activityFor($lesson);
        abort_unless($assessment, 404);
        $assessment->load('questions.options');

        if (! $this->isLessonCompleted($enrollment, $lesson)) {
            return redirect()
                ->route('student.tracks.learn', [$track, $level, $lesson])
                ->with('status', 'Finish this lesson before its Activity Questions.');
        }

        $nodes = $sequence->nodes($level);
        [$previousNode, $nextNode] = $sequence->neighboursOfActivity($nodes, $lesson);

        $attempt = $this->grading()->latestSubmittedAttempt($assessment, $user);

        return view('student.lesson-activity', [
            'track' => $track,
            'level' => $level,
            'lesson' => $lesson,
            'assessment' => $assessment,
            'attempt' => $attempt,
            'checked' => (bool) $attempt,
            'attemptsUsed' => $this->grading()->attemptsUsed($assessment, $user),
            'maxAttempts' => AssessmentGradingService::MAX_ATTEMPTS,
            'previousStep' => $this->stepLink($track, $level, $previousNode, 'Previous'),
            'nextStep' => $this->stepLink($track, $level, $nextNode, $nextNode?->type === 'activity' ? 'Activity Questions' : 'Next'),
            'isLastStep' => ! $nextNode,
        ]);
    }

    public function checkLessonAnswers(Request $request, Track $track, Level $level, Lesson $lesson, LevelSequenceService $sequence): JsonResponse
    {
        $this->authorize('learn', [$track, $this->currentRegion()]);
        $this->ensureLessonBelongsToLevel($level, $lesson);

        $assessment = $sequence->activityFor($lesson);
        abort_unless($assessment, 404);

        $user = Auth::user();
        $enrollment = $this->enrollmentFor($track);

        abort_unless($this->isLessonCompleted($enrollment, $lesson), 403, 'Finish this lesson first.');

        abort_if($this->grading()->attemptsRemaining($assessment, $user) <= 0, 403, 'No attempts remaining for these Activity Questions.');

        $validated = $request->validate([
            'answers' => ['required', 'array'],
            'answers.*' => ['nullable'],
        ]);

        $result = $this->grading()->grade($assessment, $user, $validated['answers']);

        return response()->json([
            'score' => $result['score'],
            'passed' => $result['passed'],
            'passing_score' => $assessment->passing_score,
            'feedback' => $result['feedback'],
            'reveal_answers' => $result['reveal_answers'],
            'attempts_used' => $this->grading()->attemptsUsed($assessment, $user),
            'max_attempts' => AssessmentGradingService::MAX_ATTEMPTS,
        ]);
    }

    public function complete(Track $track, Level $level, Lesson $lesson, TrackProgressService $trackProgressService, LevelSequenceService $sequence): RedirectResponse
    {
        $this->authorize('learn', [$track, $this->currentRegion()]);
        $this->authorize('view', $lesson);
        $this->ensureLessonBelongsToLevel($level, $lesson);

        $user = Auth::user();
        $enrollment = $this->enrollmentFor($track);

        $nodes = $sequence->nodes($level);
        [$previousNode, $nextNode] = $sequence->neighboursOfLesson($nodes, $lesson);

        if ($previousNode && $previousNode->type === 'activity' && ! $this->grading()->hasSubmittedAttempt($previousNode->assessment, $user)) {
            return redirect()
                ->route('student.tracks.lesson-activity', [$track, $level, $previousNode->lesson])
                ->with('status', 'Complete the Activity Questions before moving on.');
        }

        LessonProgress::query()->updateOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
            [
                'enrollment_id' => $enrollment->id,
                'status' => 'completed',
                'completed_at' => now(),
                'last_viewed_at' => now(),
            ]
        );

        $trackProgressService->recalculate($user, $track, $enrollment);

        if ($nextNode?->type === 'lesson') {
            return redirect()->route('student.tracks.learn', [$track, $level, $nextNode->lesson])
                ->with('status', 'Lesson marked as completed!');
        }

        if ($nextNode?->type === 'activity') {
            return redirect()->route('student.tracks.lesson-activity', [$track, $level, $nextNode->lesson])
                ->with('status', 'Lesson marked as completed! Now try the Activity Questions.');
        }

        $nextLevel = $track->levels()->where('number', '>', $level->number)->first();

        if ($nextLevel) {
            return redirect()->route('student.tracks.level', [$track, $nextLevel])
                ->with('status', "Level {$level->number} complete! On to level {$nextLevel->number}.");
        }

        return redirect()->route('student.tracks.show', $track)
            ->with('status', 'Track completed! Your certificate has been issued.');
    }

    public function updateVideoProgress(Request $request, Track $track, Level $level, Lesson $lesson): JsonResponse
    {
        $this->authorize('learn', [$track, $this->currentRegion()]);
        $this->authorize('view', $lesson);
        $this->ensureLessonBelongsToLevel($level, $lesson);

        $validated = $request->validate([
            'seconds' => ['required', 'integer', 'min:0'],
        ]);

        $user = Auth::user();
        $enrollment = $this->enrollmentFor($track);

        $progress = LessonProgress::query()->firstOrNew(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
        );

        $progress->enrollment_id = $enrollment->id;
        $progress->video_progress_seconds = $validated['seconds'];
        $progress->last_viewed_at = now();

        if (! in_array($progress->status, ['started', 'completed'], true)) {
            $progress->status = 'started';
            $progress->started_at ??= now();
        }

        $progress->save();

        return response()->json(['status' => 'ok']);
    }

    private function redirectToCheckpoint(Track $track, Level $level, LevelAccessService $levelAccess): RedirectResponse
    {
        $checkpointNumber = $levelAccess->nearestCheckpointNumber($level->number);
        $checkpoint = $track->levels()->where('number', $checkpointNumber)->first();

        abort_unless($checkpoint, 404);

        return redirect()
            ->route('student.tracks.level', [$track, $checkpoint])
            ->with('status', "Level {$level->number} isn't unlocked yet. You can start at level {$checkpointNumber} or finish level ".($level->number - 1).' first.');
    }

    private function ensureLessonBelongsToLevel(Level $level, Lesson $lesson): void
    {
        abort_unless($lesson->section->level_id === $level->id, 404);
    }

    /**
     * @return ?array{url: string, label: string}
     */
    private function stepLink(Track $track, Level $level, ?object $node, string $label): ?array
    {
        if (! $node) {
            return null;
        }

        // Activity Questions are shown inline on their owning lesson's page
        // now, so every step (lesson or activity node) links to that same
        // lesson URL.
        return ['url' => route('student.tracks.learn', [$track, $level, $node->lesson]), 'label' => $label];
    }

    /**
     * Whether this lesson has been marked completed — used to lock its
     * Activity Questions until the lesson itself is finished.
     */
    private function isLessonCompleted(Enrollment $enrollment, Lesson $lesson): bool
    {
        return LessonProgress::query()
            ->where('enrollment_id', $enrollment->id)
            ->where('lesson_id', $lesson->id)
            ->where('status', 'completed')
            ->exists();
    }

    private function checkedAssessmentIds($user)
    {
        return AssessmentAttempt::query()
            ->where('user_id', $user->id)
            ->where('status', 'submitted')
            ->pluck('assessment_id');
    }

    private function grading(): AssessmentGradingService
    {
        return app(AssessmentGradingService::class);
    }

    private function currentRegion(): ?string
    {
        return app(RegionPricingService::class)->resolveRegion(request());
    }

    private function enrollmentFor(Track $track): Enrollment
    {
        return Auth::user()->enrollments()
            ->where('track_id', $track->id)
            ->where('status', 'active')
            ->firstOrFail();
    }
}
