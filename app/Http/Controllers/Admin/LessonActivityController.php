<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\RedirectsWithAnchor;
use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use App\Models\Lesson;
use App\Models\Level;
use App\Models\Track;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LessonActivityController extends Controller
{
    use RedirectsWithAnchor;

    public function updateTitle(Request $request, Track $track, Level $level, Lesson $lesson): RedirectResponse
    {
        $this->authorize('update', $track);
        abort_unless($lesson->section->level_id === $level->id, 404);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $assessment = $this->assessmentFor($lesson, $validated['title']);
        $assessment->update(['title' => $validated['title']]);

        AuditLogger::log('admin.lesson_activity.title_updated', $assessment, [], $validated);

        return $this->backToAnchor($request)->with('status', 'Quiz title updated.');
    }

    public function storeQuestion(Request $request, Track $track, Level $level, Lesson $lesson): RedirectResponse
    {
        $this->authorize('update', $track);
        abort_unless($lesson->section->level_id === $level->id, 404);

        $validated = $this->validateQuestion($request);

        $assessment = $this->assessmentFor($lesson);

        $question = $assessment->questions()->create([
            'question' => $validated['question'],
            'type' => $validated['type'],
            'allow_multiple' => $validated['type'] === 'multiple_choice' && $validated['allow_multiple'],
            'correct_short_answer' => in_array($validated['type'], ['short_answer', 'fill_blank'], true) ? $validated['correct_short_answer'] : null,
            'points' => 1,
            'order' => (int) $assessment->questions()->max('order') + 1,
        ]);

        if ($validated['type'] === 'multiple_choice') {
            $correctIndexes = $validated['allow_multiple'] ? $validated['correct_options'] : [$validated['correct_option']];
            $this->syncOptions($question, $validated['options'], $correctIndexes);
        }

        AuditLogger::log('admin.activity_question.created', $question, [], $validated);

        return $this->backToAnchor($request)->with('status', 'Activity question added.');
    }

    public function updateQuestion(Request $request, Track $track, Level $level, Lesson $lesson, AssessmentQuestion $assessmentQuestion): RedirectResponse
    {
        $this->authorize('update', $track);
        abort_unless($lesson->section->level_id === $level->id, 404);
        $this->ensureQuestionBelongsToLesson($assessmentQuestion, $lesson);

        $validated = $this->validateQuestion($request);

        $previous = $assessmentQuestion->only(['question', 'type', 'correct_short_answer']);

        $assessmentQuestion->update([
            'question' => $validated['question'],
            'type' => $validated['type'],
            'allow_multiple' => $validated['type'] === 'multiple_choice' && $validated['allow_multiple'],
            'correct_short_answer' => in_array($validated['type'], ['short_answer', 'fill_blank'], true) ? $validated['correct_short_answer'] : null,
        ]);

        if ($validated['type'] === 'multiple_choice') {
            $correctIndexes = $validated['allow_multiple'] ? $validated['correct_options'] : [$validated['correct_option']];
            $this->syncOptions($assessmentQuestion, $validated['options'], $correctIndexes);
        } else {
            $assessmentQuestion->options()->delete();
        }

        AuditLogger::log('admin.activity_question.updated', $assessmentQuestion, $previous, $validated);

        return $this->backToAnchor($request)->with('status', 'Activity question updated.');
    }

    public function destroyQuestion(Track $track, Level $level, Lesson $lesson, AssessmentQuestion $assessmentQuestion): RedirectResponse
    {
        $this->authorize('update', $track);
        abort_unless($lesson->section->level_id === $level->id, 404);
        $this->ensureQuestionBelongsToLesson($assessmentQuestion, $lesson);

        AuditLogger::log('admin.activity_question.deleted', null, ['question' => $assessmentQuestion->question], []);

        $assessmentQuestion->delete();

        return back()->with('status', 'Activity question deleted.');
    }

    private function assessmentFor(Lesson $lesson, ?string $title = null): Assessment
    {
        return Assessment::query()->firstOrCreate(
            ['lesson_id' => $lesson->id],
            [
                'level_id' => $lesson->section->level_id,
                'title' => $title ?: $lesson->title.' Quiz',
                'description' => 'Answer the questions below, then press Check Answers.',
                'type' => 'quiz',
                'passing_score' => 50,
                'is_active' => true,
            ]
        );
    }

    private function ensureQuestionBelongsToLesson(AssessmentQuestion $question, Lesson $lesson): void
    {
        abort_unless($question->assessment?->lesson_id === $lesson->id, 404);
    }

    private function validateQuestion(Request $request): array
    {
        $allowMultiple = $request->boolean('allow_multiple');

        $validated = $request->validate([
            'question' => ['required', 'string', 'max:1000'],
            'type' => ['required', 'in:multiple_choice,short_answer,fill_blank'],
            'correct_short_answer' => ['required_if:type,short_answer,fill_blank', 'nullable', 'string', 'max:255'],
            'options' => ['required_if:type,multiple_choice', 'nullable', 'array', 'min:2', 'max:10'],
            'options.*' => ['nullable', 'string', 'max:255'],
            'correct_option' => [$allowMultiple ? 'exclude' : 'required_if:type,multiple_choice', 'nullable', 'integer', 'min:0'],
            'correct_options' => [$allowMultiple ? 'required_if:type,multiple_choice' : 'exclude', 'nullable', 'array', 'min:1'],
            'correct_options.*' => ['integer', 'min:0'],
        ]);

        $validated['allow_multiple'] = $allowMultiple;

        if ($validated['type'] === 'multiple_choice') {
            $validated['options'] = array_values(array_filter($validated['options'] ?? [], fn ($text) => trim((string) $text) !== ''));
            abort_if(count($validated['options']) < 2, 422, 'Provide at least two options.');
            abort_if(count($validated['options']) > 10, 422, 'A question can have at most 10 choices.');

            if ($allowMultiple) {
                abort_if(empty($validated['correct_options'] ?? []), 422, 'Mark at least one option as correct.');
            }
        }

        return $validated;
    }

    private function syncOptions(AssessmentQuestion $question, array $options, array $correctIndexes): void
    {
        $question->options()->delete();

        // Form submissions arrive as strings ("2"), but $index below is a
        // native int from the array's own keys — in_array(..., true) is a
        // strict comparison, so without this cast nothing would ever match.
        $correctIndexes = array_map('intval', $correctIndexes);

        foreach ($options as $index => $text) {
            $question->options()->create([
                'option_text' => $text,
                'is_correct' => in_array($index, $correctIndexes, true),
                'order' => $index,
            ]);
        }
    }
}
