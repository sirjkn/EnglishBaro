<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Models\Track;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LevelController extends Controller
{
    public function all(Request $request): View
    {
        $this->authorize('viewAny', Track::class);

        $tracks = Track::query()->orderBy('order')->get();

        $selectedTrack = $tracks->firstWhere('track_code', $request->query('track'))
            ?? $tracks->first();

        $levels = $selectedTrack
            ? $selectedTrack->levels()->withCount('sections')->paginate(25)
            : null;

        return view('admin.levels.all', [
            'tracks' => $tracks,
            'selectedTrack' => $selectedTrack,
            'levels' => $levels,
        ]);
    }

    public function index(Track $track): View
    {
        $this->authorize('update', $track);

        $levels = $track->levels()
            ->withCount('sections')
            ->paginate(25);

        return view('admin.levels.index', [
            'track' => $track,
            'levels' => $levels,
        ]);
    }

    public function store(Request $request, Track $track): RedirectResponse
    {
        $this->authorize('update', $track);

        $validated = $request->validate([
            'number' => ['required', 'integer', 'min:1', 'unique:levels,number,NULL,id,track_id,'.$track->id],
            'title' => ['required', 'string', 'max:255'],
        ]);

        $level = $track->levels()->create($validated);
        $level->ensureSections();

        AuditLogger::log('admin.level.created', $level, [], $validated);

        return back()->with('status', "Level {$level->number} added with its four sections.");
    }

    public function edit(Track $track, Level $level): View
    {
        $this->authorize('update', $track);

        $level->load(['sections.lessons.video']);

        return view('admin.levels.edit', [
            'track' => $track,
            'level' => $level,
        ]);
    }

    public function update(Request $request, Track $track, Level $level): RedirectResponse
    {
        $this->authorize('update', $track);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $previous = $level->only(array_keys($validated));

        $level->update($validated);

        AuditLogger::log('admin.level.updated', $level, $previous, $validated);

        return back()->with('status', 'Level updated.');
    }

    public function destroy(Track $track, Level $level): RedirectResponse
    {
        $this->authorize('update', $track);

        AuditLogger::log('admin.level.deleted', $level, $level->toArray());

        $level->delete();

        return redirect()->route('admin.tracks.levels.index', $track)->with('status', 'Level deleted.');
    }
}
