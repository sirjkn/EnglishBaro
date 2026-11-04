<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Student\MorePracticeController;
use App\Models\PracticeQuestion;
use App\Models\Track;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PracticeQuestionController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->query('category', array_key_first(MorePracticeController::CATEGORIES));

        abort_unless(array_key_exists($category, MorePracticeController::CATEGORIES), 404);

        $questions = PracticeQuestion::query()
            ->where('category', $category)
            ->with('options', 'track')
            ->orderBy('order')
            ->get();

        return view('admin.practice-questions.index', [
            'category' => $category,
            'categories' => MorePracticeController::CATEGORIES,
            'questions' => $questions,
            'tracks' => Track::query()->orderBy('order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        $question = PracticeQuestion::query()->create([
            'category' => $validated['category'],
            'track_id' => $validated['track_id'] ?: null,
            'question' => $validated['question'],
            'order' => (int) PracticeQuestion::query()->where('category', $validated['category'])->max('order') + 1,
        ]);

        foreach ($validated['options'] as $index => $optionText) {
            $question->options()->create([
                'option_text' => $optionText,
                'is_correct' => (int) $validated['correct'] === $index,
                'order' => $index,
            ]);
        }

        AuditLogger::log('admin.practice_question.created', $question, [], $validated);

        return back()->with('status', 'Practice question added.');
    }

    public function update(Request $request, PracticeQuestion $practiceQuestion): RedirectResponse
    {
        $validated = $this->validated($request);

        $previous = ['question' => $practiceQuestion->question];

        $practiceQuestion->update([
            'track_id' => $validated['track_id'] ?: null,
            'question' => $validated['question'],
        ]);

        foreach ($practiceQuestion->options as $index => $option) {
            $option->update([
                'option_text' => $validated['options'][$index] ?? $option->option_text,
                'is_correct' => (int) $validated['correct'] === $index,
            ]);
        }

        AuditLogger::log('admin.practice_question.updated', $practiceQuestion, $previous, $validated);

        return back()->with('status', 'Practice question updated.');
    }

    public function destroy(PracticeQuestion $practiceQuestion): RedirectResponse
    {
        AuditLogger::log('admin.practice_question.deleted', null, ['question' => $practiceQuestion->question], []);

        $practiceQuestion->delete();

        return back()->with('status', 'Practice question removed.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'category' => ['required', 'string', 'in:'.implode(',', array_keys(MorePracticeController::CATEGORIES))],
            'track_id' => ['nullable', 'integer', 'exists:tracks,id'],
            'question' => ['required', 'string', 'max:1000'],
            'options' => ['required', 'array', 'size:4'],
            'options.*' => ['required', 'string', 'max:255'],
            'correct' => ['required', 'integer', 'min:0', 'max:3'],
        ]);
    }
}
