<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlacementQuestion;
use App\Models\Track;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlacementQuestionController extends Controller
{
    public function index(): View
    {
        $questions = PlacementQuestion::query()->with('options.track')->orderBy('order')->get();

        return view('admin.placement-test.index', [
            'questions' => $questions,
            'tracks' => Track::query()->orderBy('order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tracks = Track::query()->orderBy('order')->get();

        $validated = $request->validate([
            'question' => ['required', 'string', 'max:1000'],
            'answers' => ['required', 'array', 'size:'.$tracks->count()],
            'answers.*' => ['required', 'string', 'max:255'],
        ]);

        $question = PlacementQuestion::query()->create([
            'question' => $validated['question'],
            'order' => (int) PlacementQuestion::query()->max('order') + 1,
        ]);

        foreach ($tracks->values() as $index => $track) {
            $question->options()->create([
                'option_text' => $validated['answers'][$track->id] ?? '',
                'track_id' => $track->id,
                'order' => $index,
            ]);
        }

        AuditLogger::log('admin.placement_question.created', $question, [], $validated);

        return back()->with('status', 'Question added to the placement test.');
    }

    public function update(Request $request, PlacementQuestion $placementQuestion): RedirectResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:1000'],
            'answers' => ['required', 'array'],
            'answers.*' => ['required', 'string', 'max:255'],
        ]);

        $previous = ['question' => $placementQuestion->question];

        $placementQuestion->update(['question' => $validated['question']]);

        foreach ($placementQuestion->options as $option) {
            if (array_key_exists($option->track_id, $validated['answers'])) {
                $option->update(['option_text' => $validated['answers'][$option->track_id]]);
            }
        }

        AuditLogger::log('admin.placement_question.updated', $placementQuestion, $previous, $validated);

        return back()->with('status', 'Question updated.');
    }

    public function destroy(PlacementQuestion $placementQuestion): RedirectResponse
    {
        AuditLogger::log('admin.placement_question.deleted', null, ['question' => $placementQuestion->question], []);

        $placementQuestion->delete();

        return back()->with('status', 'Question removed from the placement test.');
    }
}
