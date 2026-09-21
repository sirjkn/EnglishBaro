<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Testimonial;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
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
            ->orderBy('order')
            ->take(6)
            ->get();

        return view('guest.home', [
            'featuredCourses' => $featuredCourses,
            'testimonials' => $testimonials,
        ]);
    }
}
