<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Level;
use App\Models\Testimonial;
use App\Services\RegionPricingService;
use App\Services\StudentDashboardSummaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request, RegionPricingService $regionPricingService, StudentDashboardSummaryService $dashboardSummary): View
    {
        $featuredCourses = Course::query()
            ->where('status', 'published')
            ->where('is_featured', true)
            ->with(['thumbnail', 'tracks'])
            ->withCount('tracks')
            ->orderBy('order')
            ->get();

        // Social proof for an anonymous visitor deciding whether to sign up —
        // a logged-in student landing here from the journey nav has already
        // decided, so skip the query rather than fetch rows the view won't
        // render for them.
        $testimonials = auth()->guest()
            ? Testimonial::query()->where('is_published', true)->with('avatar')->orderBy('order')->take(6)->get()
            : collect();

        $stats = [
            'tracks' => Course::query()->where('status', 'published')->withCount('tracks')->get()->sum('tracks_count'),
            'levels' => Level::query()->count(),
            'lessons' => Lesson::query()->count(),
            'access_days' => Course::query()->where('status', 'published')->min('subscription_days') ?? 0,
        ];

        // A logged-in student's profile/progress summary, shown at the top
        // of Landing — the same block the Dashboard tab shows them, reused
        // here rather than making them click through to see it.
        $user = Auth::user();
        $dashboardData = $user?->isStudent()
            ? $dashboardSummary->forUser($user)
            : null;

        return view('guest.home', [
            'featuredCourses' => $featuredCourses,
            'testimonials' => $testimonials,
            'stats' => $stats,
            'pricingRegion' => $regionPricingService->resolveRegion($request),
            'dashboardData' => $dashboardData,
        ]);
    }
}
