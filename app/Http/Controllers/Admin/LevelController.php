<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LevelController extends Controller
{
    public function index(): View
    {
        $levels = Level::query()->withCount('courses')->orderBy('order')->get();

        return view('admin.levels.index', ['levels' => $levels]);
    }

    public function create(): View
    {
        return view('admin.levels.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        $level = Level::create($validated);

        AuditLogger::log('admin.level.created', $level, [], $validated);

        return redirect()->route('admin.levels.index')->with('status', 'Level created.');
    }

    public function edit(Level $level): View
    {
        return view('admin.levels.edit', ['level' => $level]);
    }

    public function update(Request $request, Level $level): RedirectResponse
    {
        $validated = $this->validated($request, $level);
        $previous = $level->only(array_keys($validated));

        $level->update($validated);

        AuditLogger::log('admin.level.updated', $level, $previous, $validated);

        return redirect()->route('admin.levels.index')->with('status', 'Level updated.');
    }

    public function destroy(Level $level): RedirectResponse
    {
        if ($level->courses()->exists()) {
            return back()->with('status', 'Cannot delete a level that has courses assigned.');
        }

        AuditLogger::log('admin.level.deleted', $level, $level->toArray());

        $level->delete();

        return redirect()->route('admin.levels.index')->with('status', 'Level deleted.');
    }

    private function validated(Request $request, ?Level $level = null): array
    {
        $name = $request->input('name');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'order' => ['required', 'integer', 'min:0'],
            'is_default' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $data['slug'] = Str::slug($name).'-'.Str::random(4);
        $data['is_default'] = $request->boolean('is_default');
        $data['is_active'] = $request->boolean('is_active', true);

        if ($level) {
            $data['slug'] = $level->slug;
        }

        if ($data['is_default']) {
            Level::query()->where('id', '!=', $level?->id)->update(['is_default' => false]);
        }

        return $data;
    }
}
