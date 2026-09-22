<x-layouts.student :title="'Track '.$track->track_code">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <span class="text-xs font-medium uppercase text-indigo-600 dark:text-indigo-400">Track {{ $track->track_code }}</span>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $track->name }}</h1>
        </div>
        <x-subscription-countdown :subscription="$enrollment?->subscription" />
    </div>

    <div class="mt-6 overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
            <thead>
                <tr class="bg-indigo-100 text-left text-xs uppercase text-indigo-900 dark:bg-indigo-950 dark:text-indigo-200 divide-x divide-white">
                    <th class="px-4 py-2">Level</th>
                    <th class="px-4 py-2">Sections</th>
                    <th class="px-4 py-2">Progress</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-indigo-100 dark:divide-gray-800 dark:bg-gray-800">
                @forelse ($levels as $level)
                    @php
                        $lessonIds = $level->sections->flatMap->lessons->pluck('id');
                        $done = $lessonIds->intersect($completedLessonIds)->count();
                    @endphp
                    <tr class="odd:bg-white even:bg-indigo-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $level->title }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                            {{ $level->sections->map(fn ($section) => $section->title)->implode(' · ') }}
                        </td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $done }} / {{ $lessonIds->count() }} lessons</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('student.tracks.level', [$track, $level]) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Open</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No levels yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $levels->links() }}</div>
</x-layouts.student>
