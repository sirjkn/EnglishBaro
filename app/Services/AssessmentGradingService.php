<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\AssessmentAnswer;
use App\Models\AssessmentAttempt;
use App\Models\User;

class AssessmentGradingService
{
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

            [$isCorrect, $correctOptionId, $correctText] = $this->gradeQuestion($question, $given);
            $isShortAnswer = $question->type === 'short_answer';

            if ($isCorrect) {
                $earnedPoints += $question->points;
            }

            AssessmentAnswer::query()->create([
                'assessment_attempt_id' => $attempt->id,
                'assessment_question_id' => $question->id,
                'assessment_option_id' => $isShortAnswer ? null : $given,
                'short_answer_text' => $isShortAnswer ? $given : null,
                'is_correct' => $isCorrect,
                // Short answers can be phrased many valid ways, so the exact-match
                // result above is only a provisional guess until an admin reviews it.
                'review_status' => $isShortAnswer ? AssessmentAnswer::REVIEW_PENDING : null,
            ]);

            $feedback[] = [
                'question_id' => $question->id,
                'correct' => $isCorrect,
                'correct_option_id' => $correctOptionId,
                'correct_text' => $correctText,
                'pending_review' => $isShortAnswer,
            ];
        }

        $score = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 100;
        $passed = $score >= $assessment->passing_score;

        $attempt->update(['score' => $score, 'passed' => $passed]);

        return ['attempt' => $attempt, 'score' => $score, 'passed' => $passed, 'feedback' => $feedback];
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
}
