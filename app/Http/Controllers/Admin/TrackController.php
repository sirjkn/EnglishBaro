<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Level;
use App\Models\Track;
use App\Services\AuditLogger;
use App\Services\MediaService;
use App\Services\TrackCurriculumGenerator;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TrackController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Track::class);

        // Grouped by course so units from different courses (English,
        // Spanish, ...) are never shown mixed in one flat list — easy to
        // mistake for one another otherwise, since they can share codes
        // like "A1" across courses.
        $courses = Course::query()
            ->with(['tracks' => fn ($query) => $query->withCount(['levels', 'enrollments', 'students'])->orderBy('order')])
            ->orderBy('order')
            ->get();

        $tracks = $courses->flatMap->tracks;

        return view('admin.tracks.index', [
            'courses' => $courses,
            'overview' => [
                'total' => $tracks->count(),
                'published' => $tracks->where('status', 'published')->count(),
                'enrollments' => Enrollment::query()->count(),
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Track::class);

        return view('admin.tracks.create', [
            'track' => new Track(['status' => 'draft', 'course_id' => $request->integer('course_id') ?: null]),
            'courses' => Course::query()->orderBy('order')->get(),
        ]);
    }

    public function store(Request $request, TrackCurriculumGenerator $curriculumGenerator): RedirectResponse
    {
        $this->authorize('create', Track::class);

        $validated = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'track_code' => ['required', 'string', 'max:10', 'alpha_dash', 'uppercase', 'unique:tracks,track_code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'learning_outcomes' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,published,archived'],
        ]);

        $validated['slug'] = Str::slug($validated['track_code'].'-'.$validated['name']);
        $validated['order'] = (int) Track::query()->where('course_id', $validated['course_id'])->max('order') + 1;
        $validated['is_active'] = true;
        $validated['is_default'] = ! Track::query()->where('course_id', $validated['course_id'])->exists();

        $track = Track::query()->create($validated);

        $curriculumGenerator->generate($track);

        AuditLogger::log('admin.track.created', $track, [], $validated);

        $redirectTo = \App\Support\SafeRedirect::resolve(
            $request->input('redirect'),
            route('admin.tracks.edit', $track)
        );

        return redirect($redirectTo)->with('status', "Track {$track->track_code} created with 100 levels.");
    }

    public function destroy(Track $track): RedirectResponse
    {
        $this->authorize('delete', $track);

        $code = $track->track_code;

        try {
            $track->delete();
        } catch (QueryException $e) {
            return redirect()->route('admin.tracks.index')
                ->with('error', "Can't delete track {$code}: it still has linked students, payments, or placement test data. Archive it instead.");
        }

        AuditLogger::log('admin.track.deleted', null, ['track_code' => $code], []);

        return redirect()->route('admin.tracks.index')->with('status', "Track {$code} deleted.");
    }

    public function edit(Track $track): View
    {
        $this->authorize('update', $track);

        $track->load('thumbnail', 'course');

        return view('admin.tracks.edit', [
            'track' => $track,
            'courses' => Course::query()->orderBy('order')->get(),
            'levelCount' => Level::query()->where('track_id', $track->id)->count(),
            'lessonCount' => $track->lessonCount(),
        ]);
    }

    public function update(Request $request, Track $track, MediaService $mediaService): RedirectResponse
    {
        $this->authorize('update', $track);

        $validated = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'learning_outcomes' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,published,archived'],
        ]);

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
