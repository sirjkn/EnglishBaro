<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Testimonial;
use App\Services\RegionPricingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request, RegionPricingService $regionPricingService): View
    {
        $featuredCourses = Course::query()
            ->where('status', 'published')
            ->where('is_featured', true)
            ->with(['level', 'thumbnail'])
            ->latest()
            ->take(6)
            ->get();

        $testimonials = Testimonial::query()
            ->where('is_published', true)
            ->with('avatar')
            ->orderBy('order')
            ->take(6)
            ->get();

        $stats = [
            'courses' => Course::query()->where('status', 'published')->count(),
            'lessons' => Lesson::query()->count(),
        ];

        return view('guest.home', [
            'featuredCourses' => $featuredCourses,
            'testimonials' => $testimonials,
            'stats' => $stats,
            'pricingRegion' => $regionPricingService->resolveRegion($request),
        ]);
    }
}
