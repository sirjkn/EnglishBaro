<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Level;
use App\Services\AuditLogger;
use App\Services\MediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Course::class);

        $query = Course::query()->with('level')->withCount(['enrollments', 'students']);

        if ($search = $request->string('search')->trim()->value()) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($levelId = $request->integer('level')) {
            $query->where('level_id', $levelId);
        }

        if ($status = $request->string('status')->value()) {
            $query->where('status', $status);
        }

        $courses = $query->latest()->paginate(15)->withQueryString();

        return view('admin.courses.index', [
            'courses' => $courses,
            'levels' => Level::query()->orderBy('order')->get(),
            'overview' => [
                'total' => Course::query()->count(),
                'published' => Course::query()->where('status', 'published')->count(),
                'enrollments' => \App\Models\Enrollment::query()->count(),
            ],
            'filters' => $request->only(['search', 'level', 'status']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Course::class);

        return view('admin.courses.create', ['levels' => Level::query()->orderBy('order')->get()]);
    }

    public function store(Request $request, MediaService $mediaService): RedirectResponse
    {
        $this->authorize('create', Course::class);

        $validated = $this->validated($request);
        $validated['thumbnail_id'] = $this->handleThumbnail($request, $mediaService);
        $validated['slug'] = Str::slug($validated['title']).'-'.Str::random(4);
        $validated['created_by'] = $request->user()->id;

        $course = Course::create($validated);

        AuditLogger::log('admin.course.created', $course, [], $validated);

        return redirect()->route('admin.courses.edit', $course)->with('status', 'Course created. Add sections and lessons below.');
    }

    public function edit(Course $course): View
    {
        $this->authorize('update', $course);

        $course->load(['sections.lessons.video', 'level', 'thumbnail']);

        return view('admin.courses.edit', [
            'course' => $course,
            'levels' => Level::query()->orderBy('order')->get(),
        ]);
    }

    public function update(Request $request, Course $course, MediaService $mediaService): RedirectResponse
    {
        $this->authorize('update', $course);

        $validated = $this->validated($request);
        $thumbnailId = $this->handleThumbnail($request, $mediaService);

        if ($thumbnailId) {
            $validated['thumbnail_id'] = $thumbnailId;
        }

        $previous = $course->only(array_keys($validated));

        $course->update($validated);

        AuditLogger::log('admin.course.updated', $course, $previous, $validated);

        return redirect()->route('admin.courses.edit', $course)->with('status', 'Course updated.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $this->authorize('delete', $course);

        AuditLogger::log('admin.course.deleted', $course, $course->toArray());

        $course->delete();

        return redirect()->route('admin.courses.index')->with('status', 'Course deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'learning_outcomes' => ['nullable', 'string'],
            'level_id' => ['nullable', 'exists:levels,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'price_africa' => ['nullable', 'numeric', 'min:0'],
            'price_europe' => ['nullable', 'numeric', 'min:0'],
            'price_north_america' => ['nullable', 'numeric', 'min:0'],
            'price_asia' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'subscription_days' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:draft,published,archived'],
            'is_featured' => ['boolean'],
        ]) + ['is_featured' => $request->boolean('is_featured')];
    }

    private function handleThumbnail(Request $request, MediaService $mediaService): ?int
    {
        if ($request->hasFile('thumbnail_file')) {
            return $mediaService->storeUploadedFile($request->file('thumbnail_file'), 'image')->id;
        }

        if ($request->filled('thumbnail_url')) {
            return $mediaService->storeExternalImage($request->input('thumbnail_url'))->id;
        }

        return null;
    }
}
