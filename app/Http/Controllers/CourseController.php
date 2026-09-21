<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $query = Course::query()
            ->where('status', 'published')
            ->with('level');

        if ($search = $request->string('search')->trim()->value()) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($levelId = $request->integer('level')) {
            $query->where('level_id', $levelId);
        }

        if ($minPrice = $request->input('min_price')) {
            $query->where('price', '>=', (float) $minPrice);
        }

        if ($maxPrice = $request->input('max_price')) {
            $query->where('price', '<=', (float) $maxPrice);
        }

        $sort = $request->string('sort')->value() ?: 'newest';

        match ($sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'title' => $query->orderBy('title'),
            default => $query->latest(),
        };

        $courses = $query->paginate(9)->withQueryString();

        $levels = Level::query()->where('is_active', true)->orderBy('order')->get();

        return view('guest.courses.index', [
            'courses' => $courses,
            'levels' => $levels,
            'filters' => $request->only(['search', 'level', 'min_price', 'max_price', 'sort']),
        ]);
    }

    public function show(Course $course): View
    {
        abort_unless($course->status === 'published', 404);

        $course->load(['level', 'sections.lessons', 'assessments', 'ebooks']);

        $isEnrolled = auth()->check()
            && $course->enrollments()->where('user_id', auth()->id())->where('status', 'active')->exists();

        return view('guest.courses.show', [
            'course' => $course,
            'isEnrolled' => $isEnrolled,
            'totalLessons' => $course->sections->sum(fn ($section) => $section->lessons->count()),
        ]);
    }
}
