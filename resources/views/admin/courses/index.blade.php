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
        class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-4"
        x-data="{ selected: {{ $openCourseId ?? 'null' }}, mode: '{{ $openCourseId ? 'edit' : 'create' }}' }"
    >
        {{-- 1/4: course list --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 lg:col-span-1">
            <div class="flex items-center justify-between gap-2 bg-indigo-600 px-3 py-2 dark:bg-indigo-800">
                <span class="text-[11px] font-semibold uppercase tracking-wide text-white">Courses</span>
                <button
                    type="button"
                    @click="mode = 'create'; selected = null"
                    class="rounded-full border border-white/40 px-2.5 py-1 text-[11px] font-semibold text-white hover:bg-white/10"
                >
                    + Add Course
                </button>
            </div>
            <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse ($courses as $course)
                    <li>
                        <button
                            type="button"
                            @click="mode = 'edit'; selected = {{ $course->id }}"
                            class="w-full px-3 py-2.5 text-left transition"
                            :class="(mode === 'edit' && selected === {{ $course->id }}) ? 'bg-indigo-50 dark:bg-indigo-900/30' : 'bg-white hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700'"
                        >
                            <div class="flex items-center justify-between gap-2">
                                <span class="flex items-center gap-1.5 min-w-0">
                                    <span class="flex-none rounded-full bg-indigo-600 px-1.5 py-0.5 text-[9px] font-bold uppercase text-white">{{ $course->course_code }}</span>
                                    <span class="truncate text-xs font-semibold text-gray-900 dark:text-white">{{ $course->name }}</span>
                                </span>
                                <x-badge :color="$course->status === 'published' ? 'green' : ($course->status === 'draft' ? 'yellow' : 'gray')">{{ ucfirst($course->status) }}</x-badge>
                            </div>
                            <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                                {{ $course->tracks_count }} unit{{ $course->tracks_count === 1 ? '' : 's' }} &middot; {{ $course->currency }} {{ number_format($course->defaultPrice(), 2) }} &middot; {{ $enrollmentCounts[$course->id] ?? 0 }} enrolled
                            </p>
                        </button>
                    </li>
                @empty
                    <li class="px-3 py-8 text-center text-xs text-gray-400">No courses found.</li>
                @endforelse
            </ul>
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
                                ? "This course has {$enrolledCount} enrolled student(s) and/or units linked to it. Remove its units first, or archive it instead."
                                : 'This removes the course. Units (tracks) must be removed or reassigned first.';
                        @endphp

                        <div class="flex flex-wrap items-center justify-between gap-2 rounded-t-xl bg-indigo-600 px-4 py-3 dark:bg-indigo-800">
                            <h2 class="text-sm font-bold text-white">Editing: {{ $course->name }} ({{ $course->course_code }})</h2>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('courses.show', $course) }}" target="_blank" class="rounded-full border border-white/40 px-3 py-1 text-xs font-medium text-white hover:bg-white/10">View Live</a>
                                <x-confirm-delete-form
                                    :action="route('admin.courses.destroy', $course)"
                                    label="Delete Course"
                                    :title="'Delete course '.$course->course_code.'?'"
                                    :message="$deleteMessage"
                                    class="rounded-full border border-red-600 bg-red-500 px-3 py-1 text-xs font-semibold text-white hover:bg-red-400"
                                />
                            </div>
                        </div>

                        <div class="rounded-b-xl border border-gray-200 p-4 dark:border-gray-700">
                            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                                <div class="grid grid-cols-3 gap-3">
                                    <x-stat-card label="Units (Tracks)" :value="$course->tracks->count()" compact class="p-3" />
                                    <x-stat-card label="Enrolled Students" :value="$enrolledCount" compact class="p-3" />
                                    <x-stat-card label="Subscription" :value="$course->subscription_days.' days'" compact class="p-3" />
                                </div>

                                <div>
                                    @include('admin.courses._thumbnail-field', ['course' => $course, 'standalone' => true])
                                </div>
                            </div>

                            <div class="mt-5 grid grid-cols-1 gap-6 lg:grid-cols-2">
                                <form id="course-form-{{ $course->id }}" method="POST" action="{{ route('admin.courses.update', $course) }}" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    @include('admin.courses._form', ['course' => $course, 'includeThumbnail' => false])

                                    <button type="submit" class="mt-6 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save Changes</button>
                                </form>

                                <div>
                                    <div class="flex items-center justify-between">
                                        <h3 class="font-semibold text-gray-900 dark:text-white">Units in this Course</h3>
                                        <a href="{{ route('admin.tracks.create', ['course_id' => $course->id]) }}" class="rounded-md bg-gradient-to-r from-indigo-600 to-indigo-500 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:from-indigo-500 hover:to-indigo-400">
                                            + Add Unit
                                        </a>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Students who buy {{ $course->name }} get every unit below. The placement test decides which one they start on.
                                    </p>

                                    <div class="mt-3 divide-y divide-gray-100 overflow-hidden rounded-xl border border-gray-200 dark:divide-gray-700 dark:border-gray-700">
                                        @forelse ($course->tracks as $track)
                                            <div class="flex items-center justify-between gap-3 bg-white px-4 py-3 dark:bg-gray-800">
                                                <div class="min-w-0">
                                                    <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">
                                                        {{ $track->track_code }} &middot; {{ $track->name }}
                                                        @if ($track->is_default)
                                                            <span class="ml-1 rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-gray-500 dark:bg-gray-700 dark:text-gray-300">Entry unit</span>
                                                        @endif
                                                    </p>
                                                    <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $track->levels_count }} levels &middot; {{ ucfirst($track->status) }}</p>
                                                </div>
                                                <div class="flex flex-none items-center gap-2">
                                                    <a href="{{ route('admin.tracks.levels.index', $track) }}" class="text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400">Levels</a>
                                                    <a href="{{ route('admin.tracks.edit', $track) }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-500">Edit</a>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="bg-white px-4 py-6 text-center text-sm text-gray-400 dark:bg-gray-800">No units yet — add one to get started.</p>
                                        @endforelse
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
