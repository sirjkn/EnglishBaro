<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Services\AuditLogger;
use App\Services\MediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function store(Request $request, Course $course, CourseSection $section, MediaService $mediaService): RedirectResponse
    {
        $this->authorize('update', $course);
        abort_unless($section->course_id === $course->id, 404);

        $validated = $this->validated($request);
        $validated['video_media_id'] = $this->handleVideo($request, $mediaService);

        $lesson = $section->lessons()->create($validated + [
            'course_id' => $course->id,
            'order' => $section->lessons()->max('order') + 1,
        ]);

        AuditLogger::log('admin.lesson.created', $lesson, [], $validated);

        return back()->with('status', 'Lesson added.');
    }

    public function update(Request $request, Course $course, Lesson $lesson, MediaService $mediaService): RedirectResponse
    {
        $this->authorize('update', $course);
        abort_unless($lesson->course_id === $course->id, 404);

        $validated = $this->validated($request);
        $videoId = $this->handleVideo($request, $mediaService);

        if ($videoId) {
            $validated['video_media_id'] = $videoId;
        }

        $previous = $lesson->only(array_keys($validated));

        $lesson->update($validated);

        AuditLogger::log('admin.lesson.updated', $lesson, $previous, $validated);

        return back()->with('status', 'Lesson updated.');
    }

    public function destroy(Course $course, Lesson $lesson): RedirectResponse
    {
        $this->authorize('update', $course);
        abort_unless($lesson->course_id === $course->id, 404);

        AuditLogger::log('admin.lesson.deleted', $lesson, $lesson->toArray());

        $lesson->delete();

        return back()->with('status', 'Lesson deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
            'is_preview' => ['boolean'],
        ]) + ['is_preview' => $request->boolean('is_preview')];
    }

    private function handleVideo(Request $request, MediaService $mediaService): ?int
    {
        if ($request->hasFile('video_file')) {
            return $mediaService->storeUploadedFile($request->file('video_file'), 'video')->id;
        }

        if ($request->filled('video_url')) {
            return $mediaService->storeExternalVideo($request->input('video_url'))->id;
        }

        return null;
    }
}
