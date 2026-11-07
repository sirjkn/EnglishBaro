<div class="space-y-4">
    @if ($hideCourseField ?? false)
        <input type="hidden" name="course_id" value="{{ $track->course_id }}">
    @else
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Course</label>
            <select name="course_id" required class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                <option value="">Select a course&hellip;</option>
                @foreach ($courses as $courseOption)
                    <option value="{{ $courseOption->id }}" @selected((int) old('course_id', $track->course_id) === $courseOption->id)>{{ $courseOption->name }} ({{ $courseOption->course_code }})</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-400">This unit is unlocked whenever a student buys this course — it isn't sold on its own.</p>
            @error('course_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    @endif

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Thumbnail</label>
        <div class="mt-2 flex items-center gap-4">
            @if ($track->thumbnail)
                <img src="{{ $track->thumbnail->resolved_url }}" class="h-20 w-20 flex-none rounded-lg object-cover">
            @else
                <div class="flex h-20 w-20 flex-none items-center justify-center rounded-lg bg-gray-100 text-gray-400 dark:bg-gray-800">
                    <x-icons.photo class="h-7 w-7" />
                </div>
            @endif
            <div class="flex-1 space-y-2">
                <input type="file" name="thumbnail_file" accept="image/*" class="block w-full text-sm text-gray-600 dark:text-gray-300">
                <input type="url" name="thumbnail_url" placeholder="https://i.imgur.com/..." class="block w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>
        </div>
        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Upload a file, or provide an external image URL (Imgur supported).</p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="min-w-0">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Track Code</label>
            @if ($creating ?? false)
                <input type="text" name="track_code" value="{{ old('track_code') }}" placeholder="e.g. C1" maxlength="10"
                       class="mt-1 w-full rounded-md border-gray-300 font-mono text-sm uppercase dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                <p class="mt-1 text-xs text-gray-400">A short unique code (letters/numbers/dashes only), e.g. C1, C2.</p>
                @error('track_code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            @else
                <input type="text" value="{{ $track->track_code }}" disabled class="mt-1 w-full rounded-md border-gray-200 bg-gray-100 font-mono text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
                <p class="mt-1 text-xs text-gray-400">The track code can't be changed after creation.</p>
            @endif
        </div>

        <div class="min-w-0">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
            <input type="text" name="name" value="{{ old('name', $track->name) }}" required class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
        <textarea name="description" rows="4" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">{{ old('description', $track->description) }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Learning Outcomes</label>
        <textarea name="learning_outcomes" rows="3" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">{{ old('learning_outcomes', $track->learning_outcomes) }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
        <select name="status" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            <option value="draft" @selected(old('status', $track->status) === 'draft')>Draft</option>
            <option value="published" @selected(old('status', $track->status) === 'published')>Published</option>
            <option value="archived" @selected(old('status', $track->status) === 'archived')>Archived</option>
        </select>
    </div>
</div>
