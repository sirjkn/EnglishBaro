<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\RedirectsWithAnchor;
use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Level;
use App\Models\Section;
use App\Models\Media;
use App\Models\Track;
use App\Services\AuditLogger;
use App\Services\MediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class LessonController extends Controller
{
    use RedirectsWithAnchor;

    private const MEDIA_FIELDS = [
        'video_files' => 'video',
        'audio_files' => 'audio',
        'image_files' => 'image',
        'document_files' => 'document',
    ];

    public function store(Request $request, Track $track, Level $level, Section $section, MediaService $mediaService): RedirectResponse
    {
        $this->authorize('update', $track);

        $validated = $this->validated($request);

        $lesson = $section->lessons()->create($validated + [
            'order' => $section->lessons()->max('order') + 1,
        ]);

        $this->attachUploads($request, $lesson, $mediaService);
        $this->attachUploadedMedia($request, $lesson);

        AuditLogger::log('admin.lesson.created', $lesson, [], $validated);

        return $this->backToAnchor($request)->with('status', 'Lesson added.');
    }

    public function update(Request $request, Track $track, Level $level, Lesson $lesson, MediaService $mediaService): RedirectResponse
    {
        $this->authorize('update', $track);
        $this->ensureLessonBelongsToLevel($level, $lesson);

        $validated = $this->validated($request);

        $previous = $lesson->only(array_keys($validated));

        $lesson->update($validated);

        $this->removeResources($request, $lesson, $mediaService);
        $this->attachUploads($request, $lesson, $mediaService);
        $this->attachUploadedMedia($request, $lesson);

        AuditLogger::log('admin.lesson.updated', $lesson, $previous, $validated);

        return $this->backToAnchor($request)->with('status', 'Lesson updated.');
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

    private function attachUploads(Request $request, Lesson $lesson, MediaService $mediaService): void
    {
        $order = (int) $lesson->resources()->max('order');

        foreach (self::MEDIA_FIELDS as $field => $type) {
            /** @var UploadedFile[] $files */
            $files = $request->file($field, []);

            foreach ($files as $file) {
                if (! $file instanceof UploadedFile || ! $file->isValid()) {
                    continue;
                }

                $media = $mediaService->storeUploadedFile($file, $type, $file->getClientOriginalName());

                $lesson->resources()->create([
                    'media_id' => $media->id,
                    'title' => $file->getClientOriginalName(),
                    'order' => ++$order,
                ]);
            }
        }
    }

    private function attachUploadedMedia(Request $request, Lesson $lesson): void
    {
        $ids = array_filter((array) $request->input('media_ids', []));

        if (empty($ids)) {
            return;
        }

        $order = (int) $lesson->resources()->max('order');

        $mediaItems = Media::whereIn('id', $ids)
            ->where('created_by', auth()->id())
            ->whereDoesntHave('resources')
            ->get();

        foreach ($mediaItems as $media) {
            $lesson->resources()->create([
                'media_id' => $media->id,
                'title' => $media->title ?? $media->original_filename,
                'order' => ++$order,
            ]);
        }
    }

    private function removeResources(Request $request, Lesson $lesson, MediaService $mediaService): void
    {
        $ids = array_filter((array) $request->input('remove_resources', []));

        if (empty($ids)) {
            return;
        }

        $resources = $lesson->resources()->with('media')->whereIn('id', $ids)->get();

        foreach ($resources as $resource) {
            $media = $resource->media;
            $resource->delete();

            if ($media) {
                $mediaService->delete($media);
            }
        }
    }
}
