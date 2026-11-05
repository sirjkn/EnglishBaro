<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\PlacementAttempt;
use App\Services\PlacementTestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PlacementTestController extends Controller
{
    public function show(PlacementTestService $placementTest): View|RedirectResponse
    {
        $user = Auth::user();

        if ($placementTest->hasCompletedPlacement($user)) {
            return redirect()->route('student.placement-test.result');
        }

        return view('student.placement-test', [
            'questions' => $placementTest->questions(),
        ]);
    }

    public function store(Request $request, PlacementTestService $placementTest): RedirectResponse
    {
        $user = Auth::user();

        if ($placementTest->hasCompletedPlacement($user)) {
            return redirect()->route('student.dashboard');
        }

        $questionIds = $placementTest->questions()->pluck('id')->all();

        $validated = $request->validate([
            'answers' => ['required', 'array', 'size:'.count($questionIds)],
            'answers.*' => ['required', 'integer', 'exists:placement_options,id'],
        ]);

        foreach ($validated['answers'] as $questionId => $optionId) {
            abort_unless(in_array((int) $questionId, $questionIds, true), 422);
        }

        $attempt = $placementTest->submit($user, $validated['answers']);

        return redirect()->route('student.placement-test.result');
    }

    public function result(): View
    {
        $user = Auth::user();

        $attempt = PlacementAttempt::query()
            ->where('user_id', $user->id)
            ->with(['track', 'answers.question', 'answers.option'])
            ->latest('completed_at')
            ->first();

        // A student's track can also be set directly by an admin, without
        // ever taking the test — there's no attempt row in that case, so
        // fall back to their assigned track instead of 404ing.
        $track = $attempt?->track ?? $user->studentProfile?->track;

        abort_if(! $track, 404);

        return view('student.placement-result', [
            'attempt' => $attempt,
            'track' => $track,
        ]);
    }
}
