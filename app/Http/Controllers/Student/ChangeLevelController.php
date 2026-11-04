<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Models\Track;
use App\Services\LevelAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChangeLevelController extends Controller
{
    public function store(Request $request, LevelAccessService $levelAccess): RedirectResponse
    {
        $validated = $request->validate([
            'track_code' => ['required', 'string', 'exists:tracks,track_code'],
            'level_number' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        abort_unless($levelAccess->isCheckpoint($validated['level_number']), 422, 'That level is not a valid starting point.');

        $track = Track::query()->where('track_code', $validated['track_code'])->firstOrFail();

        $this->authorize('learn', [$track, app(\App\Services\RegionPricingService::class)->resolveRegion($request)]);

        $level = Level::query()->where('track_id', $track->id)->where('number', $validated['level_number'])->firstOrFail();

        $enrollment = Auth::user()->enrollments()
            ->where('track_id', $track->id)
            ->where('status', 'active')
            ->firstOrFail();

        $firstLesson = $level->sections()->orderBy('order')->first()?->lessons()->orderBy('order')->first();

        $enrollment->update([
            'last_viewed_lesson_id' => $firstLesson?->id,
            'last_viewed_at' => now(),
        ]);

        Auth::user()->studentProfile?->update(['track_id' => $track->id]);

        return redirect()
            ->route('student.tracks.level', [$track, $level])
            ->with('status', "Switched to {$track->track_code} Level {$level->number}.");
    }
}
