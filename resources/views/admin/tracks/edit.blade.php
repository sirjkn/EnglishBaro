<x-layouts.admin :title="'Edit Track '.$track->track_code">
    @php
        $enrolledCount = $track->students()->count();
        $deleteMessage = $enrolledCount > 0
            ? "This track has {$enrolledCount} enrolled student(s). Deleting it removes its entire curriculum (all levels, sections and lessons) and cannot be undone."
            : 'This removes its entire curriculum (all levels, sections and lessons) and cannot be undone.';
    @endphp

    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Track {{ $track->track_code }}</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.tracks.levels.index', $track) }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">
                Manage {{ $levelCount }} Levels
            </a>
            <x-confirm-delete-form
                :action="route('admin.tracks.destroy', $track)"
                label="Delete Track"
                :title="'Delete track '.$track->track_code.'?'"
                :message="$deleteMessage"
                class="rounded-md border border-red-300 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-900/30"
            />
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card label="Levels" :value="$levelCount" />
        <x-stat-card label="Lessons" :value="$lessonCount" />
        <x-stat-card label="Subscription" :value="$track->subscription_days.' days'" />
    </div>

    <form method="POST" action="{{ route('admin.tracks.update', $track) }}" enctype="multipart/form-data" class="mt-6 max-w-3xl">
        @csrf
        @method('PUT')
        @include('admin.tracks._form', ['track' => $track])

        <button type="submit" class="mt-6 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save Changes</button>
    </form>
</x-layouts.admin>
