<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Services\AuditLogger;
use App\Services\MediaService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Course::class);

        $courses = Course::query()
            ->withCount('tracks')
            ->orderBy('order')
            ->get();

        $enrollmentCounts = Enrollment::query()
            ->join('tracks', 'tracks.id', '=', 'enrollments.track_id')
            ->selectRaw('tracks.course_id, count(distinct enrollments.user_id) as total')
            ->groupBy('tracks.course_id')
            ->pluck('total', 'course_id');

        return view('admin.courses.index', [
            'courses' => $courses,
            'enrollmentCounts' => $enrollmentCounts,
            'overview' => [
                'total' => $courses->count(),
                'published' => $courses->where('status', 'published')->count(),
                'enrollments' => Enrollment::query()->count(),
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Course::class);

        return view('admin.courses.create', [
            'course' => new Course(['price' => 0, 'currency' => 'USD', 'subscription_days' => 120, 'status' => 'draft']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Course::class);

        $validated = $this->validated($request, creating: true);

        $validated['slug'] = Str::slug($validated['course_code'].'-'.$validated['name']);
        $validated['order'] = (int) Course::query()->max('order') + 1;
        $validated['is_active'] = true;

        $course = Course::query()->create($validated);

        AuditLogger::log('admin.course.created', $course, [], $validated);

        return redirect()->route('admin.courses.edit', $course)
            ->with('status', "Course {$course->course_code} created. Now add units (tracks) to it.");
    }

    public function edit(Course $course): View
    {
        $this->authorize('update', $course);

        $course->load(['thumbnail', 'tracks' => fn ($query) => $query->withCount('levels')]);

        return view('admin.courses.edit', ['course' => $course]);
    }

    public function update(Request $request, Course $course, MediaService $mediaService): RedirectResponse
    {
        $this->authorize('update', $course);

        $validated = $this->validated($request, creating: false);

        if ($thumbnailId = $this->handleThumbnail($request, $mediaService)) {
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

        $code = $course->course_code;

        try {
            $course->delete();
        } catch (QueryException $e) {
            return redirect()->route('admin.courses.index')
                ->with('error', "Can't delete course {$code}: it still has units (tracks) or payment history linked to it. Archive it instead.");
        }

        AuditLogger::log('admin.course.deleted', null, ['course_code' => $code], []);

        return redirect()->route('admin.courses.index')->with('status', "Course {$code} deleted.");
    }

    private function validated(Request $request, bool $creating): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'learning_outcomes' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'price_africa' => ['nullable', 'numeric', 'min:0'],
            'price_europe' => ['nullable', 'numeric', 'min:0'],
            'price_north_america' => ['nullable', 'numeric', 'min:0'],
            'price_asia' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'subscription_days' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:draft,published,archived'],
            'is_featured' => ['boolean'],
        ];

        if ($creating) {
            $rules['course_code'] = ['required', 'string', 'max:10', 'alpha_dash', 'uppercase', 'unique:courses,course_code'];
        }

        return $request->validate($rules) + ['is_featured' => $request->boolean('is_featured')];
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
