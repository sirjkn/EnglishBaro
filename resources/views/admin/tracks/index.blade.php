<x-layouts.admin :title="'Course Tracks'">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Course Tracks</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.students.index') }}" class="rounded-md border border-indigo-600 px-4 py-2 text-sm font-semibold text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/30">
                Students
            </a>
            <a href="{{ route('admin.tracks.create') }}" class="rounded-md bg-gradient-to-r from-indigo-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:from-indigo-500 hover:to-indigo-400">
                + Add Course Track
            </a>
        </div>
    </div>

    <p class="mt-2 max-w-3xl text-sm text-gray-600 dark:text-gray-400">
        Units are grouped by the course they belong to — codes like A1 aren't unique across courses, so always check
        which course's section you're in before editing.
    </p>

    @if (session('error'))
        <div class="mt-4 rounded-md bg-red-50 p-3 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-300">
            {{ session('error') }}
        </div>
    @endif

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card label="Total Course Tracks" :value="$overview['total']" />
        <x-stat-card label="Published Course Tracks" :value="$overview['published']" />
        <x-stat-card label="Total Enrollments" :value="$overview['enrollments']" />
    </div>

    <div class="mt-6 space-y-8">
        @forelse ($courses as $course)
            <div>
                <div class="flex flex-wrap items-center justify-between gap-2 rounded-t-xl border border-b-0 border-indigo-200 bg-indigo-50 px-4 py-3 dark:border-indigo-900 dark:bg-indigo-950/40">
                    <div class="flex items-center gap-2">
                        <span class="rounded-full bg-indigo-600 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-white">{{ $course->course_code }}</span>
                        <h2 class="text-sm font-bold text-gray-900 dark:text-white">{{ $course->name }}</h2>
                        <x-badge :color="$course->status === 'published' ? 'green' : ($course->status === 'draft' ? 'yellow' : 'gray')">{{ ucfirst($course->status) }}</x-badge>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('admin.tracks.create', ['course_id' => $course->id]) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500">+ Add Unit</a>
                        <a href="{{ route('admin.courses.edit', $course) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500">Edit Course &rarr;</a>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-b-xl border border-gray-200 dark:border-gray-700">
                    <table class="min-w-full divide-y divide-gray-200 text-xs dark:divide-gray-700">
                        <thead>
                            <tr class="bg-indigo-600 text-left text-xs uppercase text-white dark:bg-indigo-800 dark:text-white divide-x divide-white">
                                <th class="px-4 py-2">Code</th>
                                <th class="px-4 py-2">Track</th>
                                <th class="px-4 py-2">Levels</th>
                                <th class="px-4 py-2">Students</th>
                                <th class="px-4 py-2">Status</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-indigo-100 dark:divide-gray-800 dark:bg-gray-800">
                            @forelse ($course->tracks as $track)
                                <tr class="odd:bg-white even:bg-indigo-50 dark:odd:bg-gray-800 dark:even:bg-gray-900 divide-x divide-gray-200 dark:divide-gray-700">
                                    <td class="px-4 py-3 font-mono text-xs text-gray-500 dark:text-gray-400">{{ $track->track_code }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $track->name }}</td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $track->levels_count }}</td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $track->students_count }}</td>
                                    <td class="px-4 py-3">
                                        <x-badge :color="$track->status === 'published' ? 'green' : ($track->status === 'draft' ? 'yellow' : 'gray')">{{ ucfirst($track->status) }}</x-badge>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        @php
                                            $deleteMessage = $track->students_count > 0
                                                ? "This track has {$track->students_count} enrolled student(s). Deleting it removes its entire curriculum (all levels, sections and lessons) and cannot be undone."
                                                : 'This removes its entire curriculum (all levels, sections and lessons) and cannot be undone.';
                                        @endphp
                                        <a href="{{ route('tracks.show', $track) }}" target="_blank" class="text-sm font-medium text-gray-500 hover:text-gray-700">View</a>
                                        <a href="{{ route('admin.tracks.levels.index', $track) }}" class="ml-3 text-sm font-medium text-gray-500 hover:text-gray-700">Levels</a>
                                        <a href="{{ route('admin.tracks.edit', $track) }}" class="ml-3 text-sm font-medium text-indigo-600 hover:text-indigo-500">Edit</a>
                                        <span class="ml-3 inline-block">
                                            <x-confirm-delete-form
                                                :action="route('admin.tracks.destroy', $track)"
                                                :title="'Delete track '.$track->track_code.'?'"
                                                :message="$deleteMessage"
                                            />
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400 dark:text-gray-500">No units in this course yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <p class="rounded-xl border border-gray-200 px-4 py-8 text-center text-gray-500 dark:border-gray-700 dark:text-gray-400">
                No courses yet — create one first from Course Management → Courses.
            </p>
        @endforelse
    </div>
</x-layouts.admin>
