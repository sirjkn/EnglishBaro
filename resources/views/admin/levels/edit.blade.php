<x-layouts.admin :title="'Edit '.$level->title">
    <div>
        <a href="{{ route('admin.tracks.levels.index', $track) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">&larr; {{ $track->track_code }} Levels</a>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $track->track_code }} &middot; {{ $level->title }}</h1>
    </div>

    <form method="POST" action="{{ route('admin.tracks.levels.update', [$track, $level]) }}" class="mt-6 flex max-w-xl items-end gap-3">
        @csrf
        @method('PUT')
        <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
            <input type="text" name="title" value="{{ old('title', $level->title) }}" required class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save</button>
    </form>

    <h2 class="mt-10 font-semibold text-gray-900 dark:text-white">Sections &amp; Lessons</h2>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Every level has the same four sections. Add lessons to each one.</p>

    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
        @foreach ($level->sections as $section)
            <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                <p class="font-medium text-gray-900 dark:text-white">{{ $section->title }}</p>

                <ul class="mt-3 space-y-2">
                    @forelse ($section->lessons as $lesson)
                        <li class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 text-sm dark:bg-gray-900">
                            <span class="text-gray-700 dark:text-gray-300">{{ $lesson->title }}</span>
                            <form method="POST" action="{{ route('admin.tracks.lessons.destroy', [$track, $level, $lesson]) }}" onsubmit="return confirm('Delete this lesson?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-500">Delete</button>
                            </form>
                        </li>
                    @empty
                        <li class="px-3 py-2 text-sm text-gray-400">No lessons yet.</li>
                    @endforelse
                </ul>

                <details class="mt-3">
                    <summary class="cursor-pointer text-xs font-medium text-indigo-600">+ Add Lesson</summary>
                    <form method="POST" action="{{ route('admin.tracks.lessons.store', [$track, $level, $section]) }}" enctype="multipart/form-data" class="mt-2 space-y-2">
                        @csrf
                        <input type="text" name="title" placeholder="Lesson title" required class="w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                        <textarea name="description" placeholder="Description" rows="2" class="w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white"></textarea>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="file" name="video_file" accept="video/*" class="text-xs text-gray-600 dark:text-gray-300">
                            <input type="url" name="video_url" placeholder="YouTube or video URL" class="rounded-md border-gray-300 text-xs dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                        </div>
                        <label class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                            <input type="checkbox" name="is_preview" value="1" class="rounded border-gray-300 text-indigo-600">
                            Free preview lesson
                        </label>
                        <button type="submit" class="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">Add Lesson</button>
                    </form>
                </details>
            </div>
        @endforeach
    </div>
</x-layouts.admin>
