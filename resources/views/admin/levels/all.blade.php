<x-layouts.admin :title="'Levels'">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Levels</h1>
    </div>

    <div class="mt-4 flex flex-wrap gap-2">
        @foreach ($tracks as $track)
            <a href="{{ route('admin.levels.index', ['track' => $track->track_code]) }}"
               class="rounded-full px-4 py-1.5 text-sm font-medium {{ $selectedTrack?->id === $track->id ? 'bg-indigo-600 text-white' : 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-900/40 dark:text-indigo-300' }}">
                Track {{ $track->track_code }}
            </a>
        @endforeach
    </div>

    @if ($selectedTrack)
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
                                <a href="{{ route('admin.tracks.levels.edit', [$selectedTrack, $level]) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No levels yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $levels->links() }}</div>
    @else
        <x-empty-state class="mt-8" message="No tracks yet." />
    @endif
</x-layouts.admin>
