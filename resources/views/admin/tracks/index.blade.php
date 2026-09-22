<x-layouts.admin :title="'Course Tracks'">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Course Tracks</h1>
        <a href="{{ route('admin.students.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">Students</a>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card label="Total Course Tracks" :value="$overview['total']" />
        <x-stat-card label="Published Course Tracks" :value="$overview['published']" />
        <x-stat-card label="Total Enrollments" :value="$overview['enrollments']" />
    </div>

    <div class="mt-6 overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
            <thead>
                <tr class="bg-indigo-100 text-left text-xs uppercase text-indigo-900 dark:bg-indigo-950 dark:text-indigo-200 divide-x divide-white">
                    <th class="px-4 py-2">Code</th>
                    <th class="px-4 py-2">Track</th>
                    <th class="px-4 py-2">Levels</th>
                    <th class="px-4 py-2">Price</th>
                    <th class="px-4 py-2">Students</th>
                    <th class="px-4 py-2">Subscription</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-indigo-100 dark:divide-gray-800 dark:bg-gray-800">
                @forelse ($tracks as $track)
                    <tr class="odd:bg-white even:bg-indigo-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                        <td class="px-4 py-3 font-mono text-xs text-gray-500 dark:text-gray-400">{{ $track->track_code }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $track->name }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $track->levels_count }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $track->currency }} {{ number_format((float) $track->price, 2) }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $track->students_count }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $track->subscription_days }} days</td>
                        <td class="px-4 py-3">
                            <x-badge :color="$track->status === 'published' ? 'green' : ($track->status === 'draft' ? 'yellow' : 'gray')">{{ ucfirst($track->status) }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('tracks.show', $track) }}" target="_blank" class="text-sm font-medium text-gray-500 hover:text-gray-700">View</a>
                            <a href="{{ route('admin.tracks.levels.index', $track) }}" class="ml-3 text-sm font-medium text-gray-500 hover:text-gray-700">Levels</a>
                            <a href="{{ route('admin.tracks.edit', $track) }}" class="ml-3 text-sm font-medium text-indigo-600 hover:text-indigo-500">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No tracks found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.admin>
