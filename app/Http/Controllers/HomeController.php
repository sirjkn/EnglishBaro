<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Level;
use App\Models\Testimonial;
use App\Models\Track;
use App\Services\RegionPricingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request, RegionPricingService $regionPricingService): View
    {
        $featuredTracks = Track::query()
            ->where('status', 'published')
            ->where('is_featured', true)
            ->with('thumbnail')
            ->withCount('levels')
            ->orderBy('order')
            ->get();

        // "Most popular" by actual enrollment count. With most tracks tied at
        // zero enrollments today, this just falls back to catalog order — it
        // will naturally reorder itself as real enrollments come in.
        $popularTracks = Track::query()
            ->where('status', 'published')
            ->with('thumbnail')
            ->withCount('enrollments')
            ->orderByDesc('enrollments_count')
            ->orderBy('order')
            ->take(4)
            ->get();

        $testimonials = Testimonial::query()
            ->where('is_published', true)
            ->with('avatar')
            ->orderBy('order')
            ->take(6)
            ->get();

        $stats = [
            'tracks' => Track::query()->where('status', 'published')->count(),
            'levels' => Level::query()->count(),
            'lessons' => Lesson::query()->count(),
            'access_days' => Track::query()->where('status', 'published')->min('subscription_days') ?? 0,
        ];

        return view('guest.home', [
            'featuredTracks' => $featuredTracks,
            'popularTracks' => $popularTracks,
            'testimonials' => $testimonials,
            'stats' => $stats,
            'pricingRegion' => $regionPricingService->resolveRegion($request),
        ]);
    }
}
