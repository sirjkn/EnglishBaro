<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\RegionPricingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function __invoke(Request $request, RegionPricingService $regionPricingService): View
    {
        return view('student.course', [
            'courses' => Course::query()
                ->where('status', 'published')
                ->where('is_featured', true)
                ->with(['thumbnail', 'tracks'])
                ->withCount('tracks')
                ->orderBy('order')
                ->get(),
            'pricingRegion' => $regionPricingService->resolveRegion($request),
        ]);
    }
}
