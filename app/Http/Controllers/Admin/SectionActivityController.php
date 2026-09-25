<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use App\Models\Level;
use App\Models\Section;
use App\Models\Track;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SectionActivityController extends Controller
{
    public function storeQuestion(Request $request, Track $track, Level $level, Section $section): RedirectResponse
    {
        $this->authorize('update', $track);
        abort_unless($section->level_id === $level->id, 404);

        $validated = $this->validateQuestion($request);

        $assessment = $this->assessmentFor($section);

        $question = $assessment->questions()->create([
            'question' => $validated['question'],
            'type' => $validated['type'],
            'correct_short_answer' => $validated['type'] === 'short_answer' ? $validated['correct_short_answer'] : null,
            'points' => 1,
            'order' => (int) $assessment->questions()->max('order') + 1,
        ]);

        if ($validated['type'] === 'multiple_choice') {
            $this->syncOptions($question, $validated['options'], $validated['correct_option']);
        }

        AuditLogger::log('admin.activity_question.created', $question, [], $validated);

        return back()->with('status', 'Activity question added.');
    }

    public function updateQuestion(Request $request, Track $track, Level $level, Section $section, AssessmentQuestion $assessmentQuestion): RedirectResponse
    {
        $this->authorize('update', $track);
        abort_unless($section->level_id === $level->id, 404);
        $this->ensureQuestionBelongsToSection($assessmentQuestion, $section);

        $validated = $this->validateQuestion($request);

        $previous = $assessmentQuestion->only(['question', 'type', 'correct_short_answer']);

        $assessmentQuestion->update([
            'question' => $validated['question'],
            'type' => $validated['type'],
            'correct_short_answer' => $validated['type'] === 'short_answer' ? $validated['correct_short_answer'] : null,
        ]);

        if ($validated['type'] === 'multiple_choice') {
            $this->syncOptions($assessmentQuestion, $validated['options'], $validated['correct_option']);
        } else {
            $assessmentQuestion->options()->delete();
        }

        AuditLogger::log('admin.activity_question.updated', $assessmentQuestion, $previous, $validated);

        return back()->with('status', 'Activity question updated.');
    }

    public function destroyQuestion(Track $track, Level $level, Section $section, AssessmentQuestion $assessmentQuestion): RedirectResponse
    {
        $this->authorize('update', $track);
        abort_unless($section->level_id === $level->id, 404);
        $this->ensureQuestionBelongsToSection($assessmentQuestion, $section);

        AuditLogger::log('admin.activity_question.deleted', null, ['question' => $assessmentQuestion->question], []);

        $assessmentQuestion->delete();

        return back()->with('status', 'Activity question deleted.');
    }

    private function assessmentFor(Section $section): Assessment
    {
        return Assessment::query()->firstOrCreate(
            ['section_id' => $section->id],
            [
                'level_id' => $section->level_id,
                'title' => $section->title.' Activity Questions',
                'description' => 'Answer the questions below, then press Check Answers.',
                'type' => 'quiz',
                'passing_score' => 50,
                'is_active' => true,
            ]
        );
    }

    private function ensureQuestionBelongsToSection(AssessmentQuestion $question, Section $section): void
    {
        abort_unless($question->assessment?->section_id === $section->id, 404);
    }

    private function validateQuestion(Request $request): array
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:1000'],
            'type' => ['required', 'in:multiple_choice,short_answer'],
            'correct_short_answer' => ['required_if:type,short_answer', 'nullable', 'string', 'max:255'],
            'options' => ['required_if:type,multiple_choice', 'nullable', 'array', 'min:2'],
            'options.*' => ['nullable', 'string', 'max:255'],
            'correct_option' => ['required_if:type,multiple_choice', 'nullable', 'integer', 'min:0'],
        ]);

        if ($validated['type'] === 'multiple_choice') {
            $validated['options'] = array_values(array_filter($validated['options'] ?? [], fn ($text) => trim((string) $text) !== ''));
            abort_if(count($validated['options']) < 2, 422, 'Provide at least two options.');
        }

        return $validated;
    }

    private function syncOptions(AssessmentQuestion $question, array $options, int $correctIndex): void
    {
        $question->options()->delete();

        foreach ($options as $index => $text) {
            $question->options()->create([
                'option_text' => $text,
                'is_correct' => $index === $correctIndex,
                'order' => $index,
            ]);
        }
    }
}
