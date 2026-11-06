<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Services\RegionPricingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function show(Course $course, Request $request, RegionPricingService $regionPricingService): View
    {
        abort_unless($course->status === 'published', 404);

        $course->load([
            'thumbnail',
            'tracks' => fn ($query) => $query->where('status', 'published')->with('thumbnail')->withCount('levels'),
        ]);

        $enrollment = auth()->check()
            ? $course->defaultTrack()?->enrollments()->where('user_id', auth()->id())->where('status', 'active')->first()
            : null;

        return view('guest.courses.show', [
            'course' => $course,
            'isEnrolled' => $enrollment !== null,
            'hasStartedLearning' => $enrollment?->last_viewed_lesson_id !== null,
            'pricingRegion' => $regionPricingService->resolveRegion($request),
        ]);
    }
}
