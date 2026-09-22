<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentAnswer;
use App\Models\AssessmentAttempt;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Level;
use App\Models\Track;
use App\Services\TrackProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LearningController extends Controller
{
    public function level(Track $track, Level $level): View
    {
        $this->authorize('learn', $track);

        $enrollment = $this->enrollmentFor($track);
        $level->load(['sections.lessons.video']);

        $completedLessonIds = LessonProgress::query()
            ->where('enrollment_id', $enrollment->id)
            ->where('status', 'completed')
            ->pluck('lesson_id');

        return view('student.level', [
            'track' => $track,
            'level' => $level,
            'completedLessonIds' => $completedLessonIds,
        ]);
    }

    public function resume(Track $track, Level $level): RedirectResponse
    {
        $this->authorize('learn', $track);

        $lesson = $level->lessons()->first();

        abort_if(! $lesson, 404);

        return redirect()->route('student.tracks.learn', [$track, $level, $lesson]);
    }

    public function lesson(Track $track, Level $level, Lesson $lesson): View|RedirectResponse
    {
        $this->authorize('learn', $track);
        $this->authorize('view', $lesson);
        $this->ensureLessonBelongsToLevel($level, $lesson);

        $user = Auth::user();
        $enrollment = $this->enrollmentFor($track);

        $lesson->load(['video', 'ebook.file', 'resources.media', 'section']);

        $sections = $level->sections()->with(['lessons' => fn ($query) => $query->orderBy('order')])->get();

        $completedLessonIds = LessonProgress::query()
            ->where('enrollment_id', $enrollment->id)
            ->where('status', 'completed')
            ->pluck('lesson_id');

        [$previousLesson, $nextLesson] = $this->neighbours($sections, $lesson);

        // Sequential gating: a student cannot open a lesson by URL while an
        // earlier lesson in this level still has an unchecked activity.
        if ($previousLesson && $this->lessonRequiresCheckAndIsUnchecked($previousLesson, $user)) {
            return redirect()
                ->route('student.tracks.learn', [$track, $level, $previousLesson])
                ->with('status', 'Check your answers on this activity before moving on.');
        }

        $progress = LessonProgress::query()->firstOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
            ['enrollment_id' => $enrollment->id, 'status' => 'started', 'started_at' => now()]
        );

        $progress->update(['last_viewed_at' => now()]);
        $enrollment->update(['last_viewed_lesson_id' => $lesson->id, 'last_viewed_at' => now()]);

        $assessment = $this->activeAssessmentFor($lesson);
        $assessment?->load('questions.options');

        $attempt = $assessment
            ? AssessmentAttempt::query()
                ->where('assessment_id', $assessment->id)
                ->where('user_id', $user->id)
                ->where('status', 'submitted')
                ->latest('submitted_at')
                ->with('answers')
                ->first()
            : null;

        return view('student.learn', [
            'track' => $track,
            'level' => $level,
            'lesson' => $lesson,
            'sections' => $sections,
            'completedLessonIds' => $completedLessonIds,
            'previousLesson' => $previousLesson,
            'nextLesson' => $nextLesson,
            'isCompleted' => $progress->status === 'completed',
            'assessment' => $assessment,
            'attempt' => $attempt,
            'checked' => (bool) $attempt,
        ]);
    }

    public function checkAnswers(Request $request, Track $track, Level $level, Lesson $lesson): JsonResponse
    {
        $this->authorize('learn', $track);
        $this->authorize('view', $lesson);
        $this->ensureLessonBelongsToLevel($level, $lesson);

        $assessment = $this->activeAssessmentFor($lesson);

        abort_unless($assessment, 404);

        $assessment->load('questions.options');

        $validated = $request->validate([
            'answers' => ['required', 'array'],
            'answers.*' => ['nullable'],
        ]);

        $user = Auth::user();
        $submittedAnswers = $validated['answers'];

        $attemptNumber = AssessmentAttempt::query()
            ->where('assessment_id', $assessment->id)
            ->where('user_id', $user->id)
            ->max('attempt_number') + 1;

        $attempt = AssessmentAttempt::query()->create([
            'assessment_id' => $assessment->id,
            'user_id' => $user->id,
            'attempt_number' => $attemptNumber,
            'status' => 'submitted',
            'started_at' => now(),
            'submitted_at' => now(),
        ]);

        $totalPoints = 0;
        $earnedPoints = 0;
        $feedback = [];

        foreach ($assessment->questions as $question) {
            $totalPoints += $question->points;
            $given = $submittedAnswers[$question->id] ?? null;

            [$isCorrect, $correctOptionId, $correctText] = $this->gradeQuestion($question, $given);

            if ($isCorrect) {
                $earnedPoints += $question->points;
            }

            AssessmentAnswer::query()->create([
                'assessment_attempt_id' => $attempt->id,
                'assessment_question_id' => $question->id,
                'assessment_option_id' => $question->type !== 'short_answer' ? $given : null,
                'short_answer_text' => $question->type === 'short_answer' ? $given : null,
                'is_correct' => $isCorrect,
            ]);

            $feedback[] = [
                'question_id' => $question->id,
                'correct' => $isCorrect,
                'correct_option_id' => $correctOptionId,
                'correct_text' => $correctText,
            ];
        }

        $score = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 100;
        $passed = $score >= $assessment->passing_score;

        $attempt->update(['score' => $score, 'passed' => $passed]);

        return response()->json([
            'score' => $score,
            'passed' => $passed,
            'passing_score' => $assessment->passing_score,
            'feedback' => $feedback,
        ]);
    }

    public function complete(Track $track, Level $level, Lesson $lesson, TrackProgressService $trackProgressService): RedirectResponse
    {
        $this->authorize('learn', $track);
        $this->authorize('view', $lesson);
        $this->ensureLessonBelongsToLevel($level, $lesson);

        $user = Auth::user();
        $enrollment = $this->enrollmentFor($track);

        if ($this->lessonRequiresCheckAndIsUnchecked($lesson, $user)) {
            return back()->with('status', 'Check your answers on this activity before continuing.');
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

        $sections = $level->sections()->with(['lessons' => fn ($query) => $query->orderBy('order')])->get();
        [, $nextLesson] = $this->neighbours($sections, $lesson);

        if ($nextLesson) {
            return redirect()->route('student.tracks.learn', [$track, $level, $nextLesson])
                ->with('status', 'Lesson marked as completed!');
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
        $this->authorize('learn', $track);
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

    private function ensureLessonBelongsToLevel(Level $level, Lesson $lesson): void
    {
        abort_unless($lesson->section->level_id === $level->id, 404);
    }

    private function activeAssessmentFor(Lesson $lesson): ?Assessment
    {
        return Assessment::query()
            ->where('lesson_id', $lesson->id)
            ->where('is_active', true)
            ->first();
    }

    /**
     * A lesson "requires a check" when it carries an active interactive
     * activity (currently: Grammar). The student must have a submitted
     * attempt before they may open the next lesson or mark this one complete.
     */
    private function lessonRequiresCheckAndIsUnchecked(Lesson $lesson, $user): bool
    {
        $assessment = $this->activeAssessmentFor($lesson);

        if (! $assessment) {
            return false;
        }

        return ! AssessmentAttempt::query()
            ->where('assessment_id', $assessment->id)
            ->where('user_id', $user->id)
            ->where('status', 'submitted')
            ->exists();
    }

    /**
     * @return array{0: bool, 1: ?int, 2: ?string} [isCorrect, correctOptionId, correctText]
     */
    private function gradeQuestion($question, $given): array
    {
        if ($question->type === 'short_answer') {
            $correctText = $question->correct_short_answer;
            $isCorrect = $given !== null
                && $correctText !== null
                && trim(mb_strtolower($given)) === trim(mb_strtolower($correctText));

            return [$isCorrect, null, $correctText];
        }

        $correctOption = $question->options->firstWhere('is_correct', true);
        $isCorrect = $given !== null && $correctOption && (int) $given === $correctOption->id;

        return [$isCorrect, $correctOption?->id, null];
    }

    /**
     * @return array{0: ?Lesson, 1: ?Lesson}
     */
    private function neighbours($sections, Lesson $lesson): array
    {
        $allLessons = $sections->flatMap->lessons->values();
        $index = $allLessons->search(fn ($item) => $item->id === $lesson->id);

        if ($index === false) {
            return [null, null];
        }

        return [
            $index > 0 ? $allLessons[$index - 1] : null,
            $index < $allLessons->count() - 1 ? $allLessons[$index + 1] : null,
        ];
    }

    private function enrollmentFor(Track $track): Enrollment
    {
        return Auth::user()->enrollments()
            ->where('track_id', $track->id)
            ->where('status', 'active')
            ->firstOrFail();
    }
}
