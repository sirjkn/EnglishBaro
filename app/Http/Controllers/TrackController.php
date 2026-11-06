<?php

namespace App\Http\Controllers;

use App\Models\Level;
use App\Models\Track;
use App\Services\RegionPricingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrackController extends Controller
{
    public function show(Track $track, Request $request, RegionPricingService $regionPricingService): View
    {
        abort_unless($track->status === 'published', 404);

        $track->load('thumbnail', 'course');

        // All of a track's levels are fetched in one go (never more than a few
        // hundred) so the landing page can reveal them client-side in batches
        // of 10, with no page-number pagination or extra requests.
        $levels = $track->levels()->with('sections')->get();

        $enrollment = auth()->check()
            ? $track->enrollments()->where('user_id', auth()->id())->where('status', 'active')->first()
            : null;

        return view('guest.tracks.show', [
            'track' => $track,
            'levels' => $levels,
            'isEnrolled' => $enrollment !== null,
            'hasStartedLearning' => $enrollment?->last_viewed_lesson_id !== null,
            'totalLessons' => $track->lessonCount(),
            'pricingRegion' => $regionPricingService->resolveRegion($request),
        ]);
    }

    public function level(Track $track, Level $level): View
    {
        abort_unless($track->status === 'published', 404);

        $level->load('sections.lessons');

        return view('guest.tracks.level', [
            'track' => $track,
            'level' => $level,
        ]);
    }
}
