<x-layouts.admin :title="'Courses'">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Courses</h1>

    @if (session('error'))
        <div class="mt-4 rounded-md bg-red-50 p-3 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-300">
            {{ session('error') }}
        </div>
    @endif
    @if (session('status'))
        <div class="mt-4 rounded-md bg-green-50 p-3 text-sm text-green-700 dark:bg-green-900/30 dark:text-green-300">
            {{ session('status') }}
        </div>
    @endif

    <div
        class="mt-4 grid grid-cols-1 gap-6 lg:grid-cols-4"
        x-data="{ selected: {{ $openCourseId ?? 'null' }}, mode: '{{ $openCourseId ? 'edit' : 'create' }}' }"
    >
        {{-- 1/4: course list --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 lg:col-span-1">
            <div class="flex items-center justify-between gap-2 bg-indigo-600 px-3 py-2 dark:bg-indigo-800">
                <span class="text-[11px] font-semibold uppercase tracking-wide text-white">Courses</span>
                <button
                    type="button"
                    @click="mode = 'create'; selected = null"
                    class="rounded-md border border-white/40 px-2.5 py-1 text-[11px] font-semibold text-white hover:bg-white/10"
                >
                    + Add Course
                </button>
            </div>
            <table class="min-w-full divide-y divide-gray-200 text-xs dark:divide-gray-700">
                <thead>
                    <tr class="bg-indigo-600 text-left text-[11px] uppercase text-white dark:bg-indigo-800">
                        <th class="px-3 py-2">Course</th>
                        <th class="px-3 py-2">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-indigo-100 dark:divide-gray-800">
                    @forelse ($courses as $course)
                        <tr :class="(mode === 'edit' && selected === {{ $course->id }}) ? 'bg-indigo-100 dark:bg-indigo-900/40' : 'odd:bg-white even:bg-indigo-50 hover:bg-indigo-100 dark:odd:bg-gray-800 dark:even:bg-gray-900 dark:hover:bg-gray-700'">
                            <td class="p-0" colspan="2">
                                <button
                                    type="button"
                                    @click="mode = 'edit'; selected = {{ $course->id }}"
                                    class="grid w-full grid-cols-[1fr_auto] items-center gap-2 px-3 py-2.5 text-left"
                                >
                                    <span class="min-w-0">
                                        <span class="flex items-center gap-1.5">
                                            <span class="flex-none rounded bg-indigo-600 px-1.5 py-0.5 text-[9px] font-bold uppercase text-white">{{ $course->course_code }}</span>
                                            <span class="truncate text-xs font-semibold text-gray-900 dark:text-white">{{ $course->name }}</span>
                                        </span>
                                        <span class="mt-1 block text-[11px] text-gray-500 dark:text-gray-400">
                                            {{ $course->tracks_count }} track{{ $course->tracks_count === 1 ? '' : 's' }} &middot; {{ $course->currency }} {{ number_format($course->defaultPrice(), 2) }} &middot; {{ $enrollmentCounts[$course->id] ?? 0 }} enrolled
                                        </span>
                                    </span>
                                    <x-badge :color="$course->status === 'published' ? 'green' : ($course->status === 'draft' ? 'yellow' : 'gray')">{{ ucfirst($course->status) }}</x-badge>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="px-3 py-8 text-center text-gray-400">No courses found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 3/4: toolbar, overview, and inline preview/edit panel --}}
        <div class="lg:col-span-3">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <x-stat-card label="Total Courses" :value="$overview['total']" compact class="p-3" />
                <x-stat-card label="Published Courses" :value="$overview['published']" compact class="p-3" />
                <x-stat-card label="Total Enrollments" :value="$overview['enrollments']" compact class="p-3" />
            </div>

            <div class="mt-6">
                {{-- Create panel --}}
                <div x-show="mode === 'create'" x-cloak>
                    <div class="rounded-t-xl bg-indigo-600 px-4 py-3 dark:bg-indigo-800">
                        <h2 class="text-sm font-bold text-white">Add a new course</h2>
                    </div>
                    <div class="rounded-b-xl border border-gray-200 p-4 dark:border-gray-700">
                        <form method="POST" action="{{ route('admin.courses.store') }}" enctype="multipart/form-data">
                            @csrf
                            @include('admin.courses._form', ['course' => new \App\Models\Course(['price' => 0, 'currency' => 'USD', 'subscription_days' => 120, 'status' => 'draft']), 'creating' => true])
                            <button type="submit" class="mt-6 rounded-md bg-gradient-to-r from-indigo-600 to-indigo-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:from-indigo-500 hover:to-indigo-400">Create Course</button>
                        </form>
                    </div>
                </div>

                @forelse ($courses as $course)
                    <div x-show="mode === 'edit' && selected === {{ $course->id }}" x-cloak>
                        @php
                            $enrolledCount = $enrollmentCounts[$course->id] ?? 0;
                            $deleteMessage = $enrolledCount > 0
                                ? "This course has {$enrolledCount} enrolled student(s) and/or tracks linked to it. Remove its tracks first, or archive it instead."
                                : 'This removes the course. Tracks must be removed or reassigned first.';
                        @endphp

                        <div class="flex flex-wrap items-center justify-between gap-2 rounded-t-xl bg-indigo-600 px-4 py-3 dark:bg-indigo-800">
                            <div class="flex flex-wrap items-center gap-4">
                                <h2 class="text-sm font-bold text-white">Editing: {{ $course->name }} ({{ $course->course_code }})</h2>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-md border border-yellow-300 bg-yellow-400 px-3 py-1 text-xs font-bold text-yellow-950">{{ $course->tracks->count() }} track{{ $course->tracks->count() === 1 ? '' : 's' }}</span>
                                    <span class="rounded-md border border-green-600 bg-green-500 px-3 py-1 text-xs font-bold text-white">{{ $enrolledCount }} enrolled</span>
                                    <span class="rounded-md border border-orange-600 bg-orange-500 px-3 py-1 text-xs font-bold text-white">{{ $course->subscription_days }} days</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('courses.show', $course) }}" target="_blank" class="rounded-md border border-white/40 px-3 py-1 text-xs font-medium text-white hover:bg-white/10">View Live</a>
                                <x-confirm-delete-form
                                    :action="route('admin.courses.destroy', $course)"
                                    label="Delete Course"
                                    :title="'Delete course '.$course->course_code.'?'"
                                    :message="$deleteMessage"
                                    class="rounded-md border border-red-600 bg-red-500 px-3 py-1 text-xs font-semibold text-white hover:bg-red-400"
                                />
                            </div>
                        </div>

                        <div class="rounded-b-xl border border-gray-200 p-4 dark:border-gray-700">
                            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                                <div>
                                    <div class="mb-5">
                                        @include('admin.courses._thumbnail-field', ['course' => $course, 'standalone' => true])
                                    </div>

                                    <form id="course-form-{{ $course->id }}" method="POST" action="{{ route('admin.courses.update', $course) }}" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')
                                        @include('admin.courses._form', ['course' => $course, 'includeThumbnail' => false])

                                        <button type="submit" class="mt-6 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save Changes</button>
                                    </form>
                                </div>

                                <div>
                                    <div x-data="{ addingUnit: false, editingTrack: null }">
                                        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                                            <div class="flex items-center justify-between gap-2 bg-indigo-600 px-4 py-3 dark:bg-indigo-800">
                                                <div>
                                                    <h3 class="text-sm font-bold text-white">Tracks in this Course</h3>
                                                    <p class="mt-0.5 text-[11px] text-indigo-100">Students who buy {{ $course->name }} get every track below. The placement test decides which one they start on.</p>
                                                </div>
                                                <button type="button" x-show="editingTrack === null" @click="addingUnit = ! addingUnit" class="flex-none rounded-md border border-white/40 px-3 py-1.5 text-xs font-semibold text-white hover:bg-white/10">
                                                    <span x-text="addingUnit ? 'Cancel' : '+ Add Track'"></span>
                                                </button>
                                                <button type="button" x-show="editingTrack !== null" x-cloak @click="editingTrack = null" class="flex-none rounded-md border border-white/40 px-3 py-1.5 text-xs font-semibold text-white hover:bg-white/10">
                                                    Cancel
                                                </button>
                                            </div>

                                            <div x-show="addingUnit" x-cloak class="border-t border-gray-200 p-4 dark:border-gray-700">
                                                <form method="POST" action="{{ route('admin.tracks.store') }}" enctype="multipart/form-data">
                                                    @csrf
                                                    @include('admin.tracks._form', ['track' => new \App\Models\Track(['status' => 'draft', 'course_id' => $course->id]), 'creating' => true, 'hideCourseField' => true])
                                                    <input type="hidden" name="redirect" value="{{ route('admin.courses.index', ['open' => $course->id], false) }}">
                                                    <button type="submit" class="mt-4 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Create Track</button>
                                                </form>
                                            </div>

                                            @foreach ($course->tracks as $track)
                                                @php
                                                    $courseTrackDeleteMessage = $track->students_count > 0
                                                        ? "This track has {$track->students_count} enrolled student(s). Deleting it removes its entire curriculum (all levels, sections and lessons) and cannot be undone."
                                                        : 'This removes its entire curriculum (all levels, sections and lessons) and cannot be undone.';
                                                @endphp
                                                <div x-show="editingTrack === {{ $track->id }}" x-cloak class="border-t border-gray-200 p-4 dark:border-gray-700">
                                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Editing: {{ $track->track_code }} &middot; {{ $track->name }}</h4>
                                                        <x-confirm-delete-form
                                                            :action="route('admin.tracks.destroy', $track)"
                                                            label="Delete Track"
                                                            :title="'Delete track '.$track->track_code.'?'"
                                                            :message="$courseTrackDeleteMessage"
                                                            class="rounded-md border border-red-300 px-3 py-1 text-xs font-medium text-red-600 hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-900/30"
                                                        />
                                                    </div>
                                                    <form method="POST" action="{{ route('admin.tracks.update', $track) }}" enctype="multipart/form-data" class="mt-3">
                                                        @csrf
                                                        @method('PUT')
                                                        @include('admin.tracks._form', ['track' => $track, 'hideCourseField' => true])
                                                        <input type="hidden" name="redirect" value="{{ route('admin.courses.index', ['open' => $course->id], false) }}">
                                                        <button type="submit" class="mt-4 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save Changes</button>
                                                    </form>
                                                </div>
                                            @endforeach

                                            <div x-show="! addingUnit && editingTrack === null" class="overflow-x-auto">
                                                <table class="min-w-full divide-y divide-gray-200 text-xs dark:divide-gray-700">
                                                    <thead>
                                                        <tr class="bg-indigo-600 text-left text-xs uppercase text-white dark:bg-indigo-800 dark:text-white divide-x divide-white">
                                                            <th class="px-4 py-2">Track</th>
                                                            <th class="px-4 py-2">Levels</th>
                                                            <th class="px-4 py-2">Status</th>
                                                            <th class="px-4 py-2"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-indigo-100 dark:divide-gray-800">
                                                        @forelse ($course->tracks as $track)
                                                            <tr class="odd:bg-white even:bg-indigo-50 dark:odd:bg-gray-800 dark:even:bg-gray-900 divide-x divide-gray-200 dark:divide-gray-700">
                                                                <td class="px-4 py-2 font-medium text-gray-900 dark:text-white">
                                                                    {{ $track->track_code }} &middot; {{ $track->name }}
                                                                    @if ($track->is_default)
                                                                        <span class="ml-1 rounded bg-gray-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-gray-500 dark:bg-gray-700 dark:text-gray-300">Entry track</span>
                                                                    @endif
                                                                </td>
                                                                <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $track->levels_count }}</td>
                                                                <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ ucfirst($track->status) }}</td>
                                                                <td class="px-4 py-2 text-right">
                                                                    <a href="{{ route('admin.tracks.levels.index', $track) }}" class="text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400">Levels</a>
                                                                    <button type="button" @click="editingTrack = {{ $track->id }}" class="ml-3 text-xs font-medium text-indigo-600 hover:text-indigo-500">Edit</button>
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400 dark:text-gray-500">No tracks yet — add one to get started.</td></tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex h-full items-center justify-center rounded-xl border border-dashed border-gray-300 p-12 text-center text-sm text-gray-400 dark:border-gray-600">
                        Create a course to get started.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.admin>
