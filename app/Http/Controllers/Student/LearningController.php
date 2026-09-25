<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AssessmentAttempt;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Level;
use App\Models\Section;
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
    public function level(Track $track, Level $level, LevelAccessService $levelAccess): View|RedirectResponse
    {
        $this->authorize('learn', [$track, $this->currentRegion()]);

        $enrollment = $this->enrollmentFor($track);

        if (! $levelAccess->canAccess($enrollment, $level)) {
            return $this->redirectToCheckpoint($track, $level, $levelAccess);
        }

        $level->load(['sections.lessons.video', 'sections.activity']);

        $completedLessonIds = LessonProgress::query()
            ->where('enrollment_id', $enrollment->id)
            ->where('status', 'completed')
            ->pluck('lesson_id');

        $checkedAssessmentIds = $this->checkedAssessmentIds(Auth::user());

        return view('student.level', [
            'track' => $track,
            'level' => $level,
            'completedLessonIds' => $completedLessonIds,
            'checkedAssessmentIds' => $checkedAssessmentIds,
        ]);
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

        $lesson->load(['video', 'ebook.file', 'resources.media', 'section']);

        $nodes = $sequence->nodes($level);
        [$previousNode, $nextNode] = $sequence->neighboursOfLesson($nodes, $lesson);

        // Sequential gating: a student cannot skip past a section's Activity
        // Questions by jumping straight to the URL of the next section's lesson.
        if ($previousNode && $previousNode->type === 'activity' && ! $this->grading()->hasSubmittedAttempt($previousNode->assessment, $user)) {
            return redirect()
                ->route('student.tracks.section-activity', [$track, $level, $previousNode->section])
                ->with('status', 'Complete the Activity Questions before moving on.');
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

        return view('student.learn', [
            'track' => $track,
            'level' => $level,
            'lesson' => $lesson,
            'sections' => $level->sections()->with(['lessons' => fn ($query) => $query->orderBy('order'), 'activity'])->get(),
            'completedLessonIds' => $completedLessonIds,
            'previousStep' => $this->stepLink($track, $level, $previousNode, 'Previous'),
            'nextStep' => $this->stepLink($track, $level, $nextNode, $nextNode?->type === 'activity' ? 'Activity Questions' : 'Next'),
            'isCompleted' => $progress->status === 'completed',
        ]);
    }

    public function sectionActivity(Track $track, Level $level, Section $section, LevelAccessService $levelAccess, LevelSequenceService $sequence): View|RedirectResponse
    {
        $this->authorize('learn', [$track, $this->currentRegion()]);
        abort_unless($section->level_id === $level->id, 404);

        $user = Auth::user();
        $enrollment = $this->enrollmentFor($track);

        if (! $levelAccess->canAccess($enrollment, $level)) {
            return $this->redirectToCheckpoint($track, $level, $levelAccess);
        }

        $assessment = $sequence->activityFor($section);
        abort_unless($assessment, 404);
        $assessment->load('questions.options');

        $incompleteLesson = $this->firstIncompleteLesson($enrollment, $section);

        if ($incompleteLesson) {
            return redirect()
                ->route('student.tracks.learn', [$track, $level, $incompleteLesson])
                ->with('status', 'Finish this section\'s lessons before the Activity Questions.');
        }

        $nodes = $sequence->nodes($level);
        [$previousNode, $nextNode] = $sequence->neighboursOfActivity($nodes, $section);

        if ($previousNode && $previousNode->type === 'activity' && ! $this->grading()->hasSubmittedAttempt($previousNode->assessment, $user)) {
            return redirect()
                ->route('student.tracks.section-activity', [$track, $level, $previousNode->section])
                ->with('status', 'Complete the Activity Questions before moving on.');
        }

        $attempt = $this->grading()->latestSubmittedAttempt($assessment, $user);

        return view('student.section-activity', [
            'track' => $track,
            'level' => $level,
            'section' => $section,
            'assessment' => $assessment,
            'attempt' => $attempt,
            'checked' => (bool) $attempt,
            'previousStep' => $this->stepLink($track, $level, $previousNode, 'Previous'),
            'nextStep' => $this->stepLink($track, $level, $nextNode, $nextNode?->type === 'activity' ? 'Activity Questions' : 'Next'),
            'isLastStep' => ! $nextNode,
        ]);
    }

    public function checkSectionAnswers(Request $request, Track $track, Level $level, Section $section, LevelSequenceService $sequence): JsonResponse
    {
        $this->authorize('learn', [$track, $this->currentRegion()]);
        abort_unless($section->level_id === $level->id, 404);

        $assessment = $sequence->activityFor($section);
        abort_unless($assessment, 404);

        $user = Auth::user();
        $enrollment = $this->enrollmentFor($track);

        abort_if($this->firstIncompleteLesson($enrollment, $section), 403, 'Finish this section\'s lessons first.');

        $nodes = $sequence->nodes($level);
        [$previousNode] = $sequence->neighboursOfActivity($nodes, $section);

        if ($previousNode && $previousNode->type === 'activity' && ! $this->grading()->hasSubmittedAttempt($previousNode->assessment, $user)) {
            abort(403, 'Complete the previous section\'s Activity Questions first.');
        }

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
                ->route('student.tracks.section-activity', [$track, $level, $previousNode->section])
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
            return redirect()->route('student.tracks.section-activity', [$track, $level, $nextNode->section])
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

        $url = $node->type === 'lesson'
            ? route('student.tracks.learn', [$track, $level, $node->lesson])
            : route('student.tracks.section-activity', [$track, $level, $node->section]);

        return ['url' => $url, 'label' => $label];
    }

    /**
     * The first lesson in this section the student hasn't completed yet, or
     * null if every lesson is done — used to lock Activity Questions until
     * the section's lessons are finished.
     */
    private function firstIncompleteLesson(Enrollment $enrollment, Section $section): ?Lesson
    {
        $completedLessonIds = LessonProgress::query()
            ->where('enrollment_id', $enrollment->id)
            ->where('status', 'completed')
            ->pluck('lesson_id');

        return $section->lessons()
            ->whereNotIn('id', $completedLessonIds)
            ->orderBy('order')
            ->first();
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
