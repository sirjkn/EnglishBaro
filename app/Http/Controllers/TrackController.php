<?php

namespace App\Http\Controllers;

use App\Models\Level;
use App\Models\Track;
use App\Services\RegionPricingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrackController extends Controller
{
    public function index(Request $request, RegionPricingService $regionPricingService): View
    {
        $query = Track::query()
            ->where('status', 'published')
            ->with('thumbnail')
            ->withCount('levels');

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('track_code', 'like', "%{$search}%");
            });
        }

        $sort = $request->string('sort')->value() ?: 'order';

        match ($sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'name' => $query->orderBy('name'),
            default => $query->orderBy('order'),
        };

        return view('guest.tracks.index', [
            'tracks' => $query->get(),
            'filters' => $request->only(['search', 'sort']),
            'pricingRegion' => $regionPricingService->resolveRegion($request),
        ]);
    }

    public function show(Track $track, Request $request, RegionPricingService $regionPricingService): View
    {
        abort_unless($track->status === 'published', 404);

        $track->load('thumbnail');

        $levels = $track->levels()->with('sections')->paginate(20)->withQueryString();

        $isEnrolled = auth()->check()
            && $track->enrollments()->where('user_id', auth()->id())->where('status', 'active')->exists();

        return view('guest.tracks.show', [
            'track' => $track,
            'levels' => $levels,
            'isEnrolled' => $isEnrolled,
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
