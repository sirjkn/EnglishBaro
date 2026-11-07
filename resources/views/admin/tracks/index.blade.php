<x-layouts.admin :title="'Course Tracks'">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Course Tracks</h1>
        <a href="{{ route('admin.students.index') }}" class="rounded-md border border-indigo-600 px-4 py-2 text-sm font-semibold text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/30">
            Students
        </a>
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

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-4" x-data="{ selected: {{ $courses->first()?->id ?? 'null' }} }">
        {{-- 1/4: courses, stacked in one column --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 lg:col-span-1">
            <div class="bg-indigo-600 px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-white dark:bg-indigo-800">
                Courses
            </div>
            <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse ($courses as $course)
                    <li>
                        <button
                            type="button"
                            @click="selected = {{ $course->id }}"
                            class="w-full px-3 py-2.5 text-left transition"
                            :class="selected === {{ $course->id }} ? 'bg-indigo-50 dark:bg-indigo-900/30' : 'bg-white hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700'"
                        >
                            <div class="flex items-center justify-between gap-2">
                                <span class="flex items-center gap-1.5 min-w-0">
                                    <span class="flex-none rounded bg-indigo-600 px-1.5 py-0.5 text-[9px] font-bold uppercase text-white">{{ $course->course_code }}</span>
                                    <span class="truncate text-xs font-semibold text-gray-900 dark:text-white">{{ $course->name }}</span>
                                </span>
                                <x-badge :color="$course->status === 'published' ? 'green' : ($course->status === 'draft' ? 'yellow' : 'gray')">{{ ucfirst($course->status) }}</x-badge>
                            </div>
                            <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                                {{ $course->tracks->count() }} unit{{ $course->tracks->count() === 1 ? '' : 's' }}
                            </p>
                        </button>
                    </li>
                @empty
                    <li class="px-3 py-8 text-center text-xs text-gray-400">No courses found.</li>
                @endforelse
            </ul>
        </div>

        {{-- 3/4: selected course's units --}}
        <div class="lg:col-span-3">
            @forelse ($courses as $course)
                <div x-show="selected === {{ $course->id }}" x-cloak>
                    <div class="flex flex-wrap items-center justify-between gap-2 rounded-t-xl bg-indigo-600 px-4 py-3 dark:bg-indigo-800">
                        <div class="flex flex-wrap items-center gap-3">
                            <h2 class="text-sm font-bold text-white">{{ $course->name }} ({{ $course->course_code }})</h2>
                            <x-badge :color="$course->status === 'published' ? 'green' : ($course->status === 'draft' ? 'yellow' : 'gray')">{{ ucfirst($course->status) }}</x-badge>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.tracks.create', ['course_id' => $course->id]) }}" class="rounded-md border border-green-600 bg-green-500 px-3 py-1 text-xs font-semibold text-white hover:bg-green-400">+ Add Unit</a>
                            <a href="{{ route('admin.courses.edit', $course) }}" class="rounded-md border border-orange-600 bg-orange-500 px-3 py-1 text-xs font-semibold text-white hover:bg-orange-400">Edit Course &rarr;</a>
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
                                            <a href="{{ route('tracks.show', $track) }}" target="_blank" class="rounded border border-gray-300 px-2 py-1 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">View</a>
                                            <a href="{{ route('admin.tracks.levels.index', $track) }}" class="ml-2 rounded border border-gray-300 px-2 py-1 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">Levels</a>
                                            <a href="{{ route('admin.tracks.edit', $track) }}" class="ml-2 rounded border border-indigo-600 px-2 py-1 text-xs font-medium text-indigo-600 hover:bg-indigo-600 hover:text-white dark:border-indigo-400 dark:text-indigo-400">Edit</a>
                                            <span class="ml-2 inline-block">
                                                <x-confirm-delete-form
                                                    :action="route('admin.tracks.destroy', $track)"
                                                    :title="'Delete track '.$track->track_code.'?'"
                                                    :message="$deleteMessage"
                                                    class="rounded border border-red-600 px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-600 hover:text-white dark:border-red-500 dark:text-red-400"
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
                <div class="flex h-full items-center justify-center rounded-xl border border-dashed border-gray-300 p-12 text-center text-sm text-gray-400 dark:border-gray-600">
                    No courses yet — create one first from Course Management &rarr; Courses.
                </div>
            @endforelse
        </div>
    </div>
</x-layouts.admin>
