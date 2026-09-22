<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Level;
use App\Models\Section;
use App\Models\Track;
use App\Services\AuditLogger;
use App\Services\MediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function store(Request $request, Track $track, Level $level, Section $section, MediaService $mediaService): RedirectResponse
    {
        $this->authorize('update', $track);

        $validated = $this->validated($request);
        $validated['video_media_id'] = $this->handleVideo($request, $mediaService);

        $lesson = $section->lessons()->create($validated + [
            'order' => $section->lessons()->max('order') + 1,
        ]);

        AuditLogger::log('admin.lesson.created', $lesson, [], $validated);

        return back()->with('status', 'Lesson added.');
    }

    public function update(Request $request, Track $track, Level $level, Lesson $lesson, MediaService $mediaService): RedirectResponse
    {
        $this->authorize('update', $track);
        $this->ensureLessonBelongsToLevel($level, $lesson);

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

    public function destroy(Track $track, Level $level, Lesson $lesson): RedirectResponse
    {
        $this->authorize('update', $track);
        $this->ensureLessonBelongsToLevel($level, $lesson);

        AuditLogger::log('admin.lesson.deleted', $lesson, $lesson->toArray());

        $lesson->delete();

        return back()->with('status', 'Lesson deleted.');
    }

    private function ensureLessonBelongsToLevel(Level $level, Lesson $lesson): void
    {
        abort_unless($lesson->section->level_id === $level->id, 404);
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
