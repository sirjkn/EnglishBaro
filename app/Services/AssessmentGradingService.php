<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\AssessmentAnswer;
use App\Models\AssessmentAttempt;
use App\Models\User;

class AssessmentGradingService
{
    public const MAX_ATTEMPTS = 3;

    public function attemptsUsed(Assessment $assessment, User $user): int
    {
        return AssessmentAttempt::query()
            ->where('assessment_id', $assessment->id)
            ->where('user_id', $user->id)
            ->where('status', 'submitted')
            ->count();
    }

    public function attemptsRemaining(Assessment $assessment, User $user): int
    {
        return max(0, self::MAX_ATTEMPTS - $this->attemptsUsed($assessment, $user));
    }

    /**
     * @param  array<int, mixed>  $submittedAnswers  [question_id => option_id|text]
     * @return array{attempt: AssessmentAttempt, score: float, passed: bool, feedback: array}
     */
    public function grade(Assessment $assessment, User $user, array $submittedAnswers): array
    {
        $assessment->loadMissing('questions.options');

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

            [$isCorrect, $correctOptionId, $correctText, $correctOptionIds] = $this->gradeQuestion($question, $given);
            $isTextAnswer = in_array($question->type, ['short_answer', 'fill_blank'], true);
            $isMultiSelect = $question->type === 'multiple_choice' && $question->allow_multiple;
            // Short Answer and Fill-the-Blank both grade against the admin's
            // correct-answer text by exact match — no manual review queue.
            $needsReview = false;

            if ($isCorrect) {
                $earnedPoints += $question->points;
            }

            AssessmentAnswer::query()->create([
                'assessment_attempt_id' => $attempt->id,
                'assessment_question_id' => $question->id,
                'assessment_option_id' => (! $isTextAnswer && ! $isMultiSelect) ? $given : null,
                'selected_option_ids' => $isMultiSelect ? array_map('intval', (array) $given) : null,
                'short_answer_text' => $isTextAnswer ? $given : null,
                'is_correct' => $isCorrect,
                'review_status' => $needsReview ? AssessmentAnswer::REVIEW_PENDING : null,
            ]);

            $feedback[] = [
                'question_id' => $question->id,
                'correct' => $isCorrect,
                'correct_option_id' => $correctOptionId,
                'correct_option_ids' => $correctOptionIds,
                'correct_text' => $correctText,
                'pending_review' => $needsReview,
            ];
        }

        $score = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 100;
        $passed = $score >= $assessment->passing_score;

        $attempt->update(['score' => $score, 'passed' => $passed]);

        // Correct answers stay hidden on failed attempts so retrying is
        // meaningful — only a pass, or running out of attempts, reveals them.
        $revealAnswers = $passed || $attemptNumber >= self::MAX_ATTEMPTS;

        if (! $revealAnswers) {
            $feedback = array_map(fn ($item) => [
                ...$item,
                'correct_option_id' => null,
                'correct_option_ids' => null,
                'correct_text' => null,
            ], $feedback);
        }

        return [
            'attempt' => $attempt,
            'score' => $score,
            'passed' => $passed,
            'feedback' => $feedback,
            'reveal_answers' => $revealAnswers,
        ];
    }

    /**
     * An admin's decision on one short-answer response. Updates that answer
     * and recomputes its attempt's overall score/passed state.
     */
    public function reviewShortAnswer(AssessmentAnswer $answer, bool $approve, ?string $remarks, User $reviewer): void
    {
        $answer->update([
            'is_correct' => $approve,
            'review_status' => $approve ? AssessmentAnswer::REVIEW_APPROVED : AssessmentAnswer::REVIEW_REJECTED,
            'review_remarks' => $remarks,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        $this->recalculateAttemptScore($answer->attempt);
    }

    public function recalculateAttemptScore(AssessmentAttempt $attempt): void
    {
        $attempt->loadMissing(['answers.question', 'assessment']);

        $totalPoints = 0;
        $earnedPoints = 0;

        foreach ($attempt->answers as $answer) {
            $points = $answer->question->points;
            $totalPoints += $points;

            if ($answer->is_correct) {
                $earnedPoints += $points;
            }
        }

        $score = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 100;
        $passed = $score >= $attempt->assessment->passing_score;

        $attempt->update(['score' => $score, 'passed' => $passed]);
    }

    public function hasSubmittedAttempt(Assessment $assessment, User $user): bool
    {
        return AssessmentAttempt::query()
            ->where('assessment_id', $assessment->id)
            ->where('user_id', $user->id)
            ->where('status', 'submitted')
            ->exists();
    }

    public function latestSubmittedAttempt(Assessment $assessment, User $user): ?AssessmentAttempt
    {
        return AssessmentAttempt::query()
            ->where('assessment_id', $assessment->id)
            ->where('user_id', $user->id)
            ->where('status', 'submitted')
            ->latest('submitted_at')
            ->with('answers')
            ->first();
    }

    /**
     * @return array{0: bool, 1: ?int, 2: ?string, 3: ?array} [isCorrect, correctOptionId, correctText, correctOptionIds]
     */
    private function gradeQuestion($question, $given): array
    {
        if ($question->type === 'fill_blank') {
            $correctText = $question->correct_short_answer;
            // Admins can list several acceptable answers separated by commas
            // (e.g. "blue, azure, sky blue") since a blank can have more than
            // one valid word.
            $accepted = collect(explode(',', (string) $correctText))
                ->map(fn ($candidate) => trim(mb_strtolower($candidate)))
                ->filter();

            $isCorrect = $given !== null && $accepted->contains(trim(mb_strtolower($given)));

            return [$isCorrect, null, $correctText, null];
        }

        if ($question->type === 'short_answer') {
            $correctText = $question->correct_short_answer;
            // Same as fill-the-blank: admins can list several acceptable
            // phrasings separated by commas for free-text auto-grading.
            $accepted = collect(explode(',', (string) $correctText))
                ->map(fn ($candidate) => trim(mb_strtolower($candidate)))
                ->filter();

            $isCorrect = $given !== null && $accepted->contains(trim(mb_strtolower($given)));

            return [$isCorrect, null, $correctText, null];
        }

        if ($question->allow_multiple) {
            $correctOptionIds = $question->options->where('is_correct', true)->pluck('id')->sort()->values()->all();
            $givenIds = collect((array) $given)->map(fn ($id) => (int) $id)->sort()->values()->all();

            $isCorrect = $givenIds === $correctOptionIds;

            return [$isCorrect, null, null, $correctOptionIds];
        }

        $correctOption = $question->options->firstWhere('is_correct', true);
        $isCorrect = $given !== null && $correctOption && (int) $given === $correctOption->id;

        return [$isCorrect, $correctOption?->id, null, null];
    }
}
