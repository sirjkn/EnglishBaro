<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Level;
use App\Models\Track;
use App\Services\LevelAccessService;
use App\Services\RegionPricingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MyTrackController extends Controller
{
    public function index(): View
    {
        $enrollments = Auth::user()->enrollments()
            ->with(['track.thumbnail', 'progress', 'subscription'])
            ->latest('enrolled_at')
            ->get();

        return view('student.tracks.index', [
            'enrollments' => $enrollments,
        ]);
    }

    /**
     * There's no standalone track overview page: a student goes straight
     * into their course material, landing on whichever level they're
     * currently on (or level 1 if they haven't started).
     */
    public function show(Track $track, LevelAccessService $levelAccess, RegionPricingService $regionPricingService): RedirectResponse
    {
        $this->authorize('learn', [$track, $regionPricingService->resolveRegion(request())]);

        $enrollment = Auth::user()->enrollments()
            ->where('track_id', $track->id)
            ->where('status', 'active')
            ->first();

        $targetLevel = $enrollment
            ? $this->currentLevelFor($enrollment, $track, $levelAccess)
            : $track->levels()->orderBy('number')->first();

        abort_unless($targetLevel, 404);

        return redirect()->route('student.tracks.level', [$track, $targetLevel]);
    }

    /**
     * The first unlocked level the student hasn't finished yet, or the last
     * level in the track if everything's complete.
     */
    private function currentLevelFor(Enrollment $enrollment, Track $track, LevelAccessService $levelAccess): ?Level
    {
        $statuses = $levelAccess->statusesFor($enrollment, $track);

        $current = collect($statuses)->first(fn ($status) => $status['unlocked'] && ! $status['completed']);

        return $current['level'] ?? $track->levels()->orderByDesc('number')->first();
    }
}
