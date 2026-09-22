<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Level;
use App\Models\Track;
use App\Services\AuditLogger;
use App\Services\MediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrackController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Track::class);

        $tracks = Track::query()
            ->withCount(['levels', 'enrollments', 'students'])
            ->orderBy('order')
            ->get();

        return view('admin.tracks.index', [
            'tracks' => $tracks,
            'overview' => [
                'total' => $tracks->count(),
                'published' => $tracks->where('status', 'published')->count(),
                'enrollments' => Enrollment::query()->count(),
            ],
        ]);
    }

    public function edit(Track $track): View
    {
        $this->authorize('update', $track);

        $track->load('thumbnail');

        return view('admin.tracks.edit', [
            'track' => $track,
            'levelCount' => Level::query()->where('track_id', $track->id)->count(),
            'lessonCount' => $track->lessonCount(),
        ]);
    }

    public function update(Request $request, Track $track, MediaService $mediaService): RedirectResponse
    {
        $this->authorize('update', $track);

        $validated = $request->validate([
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
        ]) + ['is_featured' => $request->boolean('is_featured')];

        if ($thumbnailId = $this->handleThumbnail($request, $mediaService)) {
            $validated['thumbnail_id'] = $thumbnailId;
        }

        $previous = $track->only(array_keys($validated));

        $track->update($validated);

        AuditLogger::log('admin.track.updated', $track, $previous, $validated);

        return redirect()->route('admin.tracks.edit', $track)->with('status', 'Track updated.');
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
