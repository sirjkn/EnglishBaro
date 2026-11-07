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
    public function index(Request $request, Track $track): View
    {
        $this->authorize('update', $track);

        // Levels are listed lightly (just counts) in the 1/4 list; only the
        // selected level's full section/lesson/activity tree is loaded, to
        // avoid pulling that deep tree for all 25 levels on the page at once.
        $levels = $track->levels()
            ->withCount('sections')
            ->orderBy('number')
            ->paginate(25);

        $openLevelId = $request->integer('open') ?: null;
        $openLevel = $openLevelId
            ? $track->levels()
                ->with(['sections.lessons.video', 'sections.lessons.image', 'sections.lessons.resources.media', 'sections.lessons.activity.questions.options'])
                ->find($openLevelId)
            : null;

        return view('admin.levels.index', [
            'track' => $track,
            'levels' => $levels,
            'openLevel' => $openLevel,
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

    public function edit(Track $track, Level $level): RedirectResponse
    {
        $this->authorize('update', $track);

        // The standalone edit page was retired in favor of the inline panel
        // on the levels list.
        return redirect()->route('admin.tracks.levels.index', [$track, 'open' => $level->id]);
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
