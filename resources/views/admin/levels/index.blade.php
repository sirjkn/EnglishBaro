<x-layouts.admin :title="'Levels - '.$track->track_code">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('admin.tracks.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">&larr; Tracks</a>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $track->track_code }} &middot; Levels</h1>
        </div>
        <a href="{{ route('admin.tracks.edit', $track) }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">Edit Track</a>
    </div>

    <details class="mt-6 rounded-xl border border-dashed border-gray-300 p-4 dark:border-gray-700">
        <summary class="cursor-pointer text-sm font-medium text-indigo-600">+ Add Level</summary>
        <form method="POST" action="{{ route('admin.tracks.levels.store', $track) }}" class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-3">
            @csrf
            <input type="number" name="number" min="1" placeholder="Level number" required class="rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            <input type="text" name="title" placeholder="Level title" required class="rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            <button type="submit" class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500">Add Level</button>
        </form>
        @error('number') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </details>

    <div class="mt-6 overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
            <thead>
                <tr class="bg-indigo-600 text-left text-xs uppercase text-white dark:bg-indigo-800 dark:text-white divide-x divide-white">
                    <th class="px-4 py-2">#</th>
                    <th class="px-4 py-2">Title</th>
                    <th class="px-4 py-2">Sections</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-indigo-100 dark:divide-gray-800 dark:bg-gray-800">
                @forelse ($levels as $level)
                    <tr class="odd:bg-white even:bg-indigo-50 dark:odd:bg-gray-800 dark:even:bg-gray-900 divide-x divide-gray-200 dark:divide-gray-700">
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $level->number }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $level->title }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $level->sections_count }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.tracks.levels.edit', [$track, $level]) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Edit</a>
                            <form method="POST" action="{{ route('admin.tracks.levels.destroy', [$track, $level]) }}" class="inline" onsubmit="return confirm('Delete this level and all its sections and lessons?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="ml-3 text-sm font-medium text-red-600 hover:text-red-500">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No levels yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $levels->links() }}</div>
</x-layouts.admin>
