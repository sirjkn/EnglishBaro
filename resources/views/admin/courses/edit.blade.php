<x-layouts.admin :title="'Edit Course '.$course->course_code">
    @php
        $enrolledCount = \App\Models\Enrollment::query()
            ->join('tracks', 'tracks.id', '=', 'enrollments.track_id')
            ->where('tracks.course_id', $course->id)
            ->distinct('enrollments.user_id')
            ->count('enrollments.user_id');
        $deleteMessage = $enrolledCount > 0
            ? "This course has {$enrolledCount} enrolled student(s) and/or units linked to it. Remove its units first, or archive it instead."
            : 'This removes the course. Units (tracks) must be removed or reassigned first.';
    @endphp

    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Course {{ $course->course_code }}</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.courses.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">
                &larr; Back to Courses
            </a>
            <x-confirm-delete-form
                :action="route('admin.courses.destroy', $course)"
                label="Delete Course"
                :title="'Delete course '.$course->course_code.'?'"
                :message="$deleteMessage"
                class="rounded-md border border-red-300 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-900/30"
            />
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card label="Units (Tracks)" :value="$course->tracks->count()" />
        <x-stat-card label="Enrolled Students" :value="$enrolledCount" />
        <x-stat-card label="Subscription" :value="$course->subscription_days.' days'" />
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <form method="POST" action="{{ route('admin.courses.update', $course) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.courses._form', ['course' => $course])

            <button type="submit" class="mt-6 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save Changes</button>
        </form>

        <div>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-gray-900 dark:text-white">Units in this Course</h2>
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
</x-layouts.admin>
