<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
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
            return redirect()->route('student.dashboard')
                ->with('status', 'You have already completed your placement test.');
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

        return redirect()->route('student.dashboard')
            ->with('status', "You've been placed into track {$attempt->track->track_code}. You can now enroll in {$attempt->track->track_code} and any track below it.");
    }
}
