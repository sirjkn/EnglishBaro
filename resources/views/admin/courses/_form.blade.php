<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
        <input type="text" name="title" value="{{ old('title', $course?->title) }}" required class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
        @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
        <textarea name="description" rows="4" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">{{ old('description', $course?->description) }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Learning Outcomes</label>
        <textarea name="learning_outcomes" rows="3" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">{{ old('learning_outcomes', $course?->learning_outcomes) }}</textarea>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Level</label>
            <select name="level_id" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                <option value="">All Levels</option>
                @foreach ($levels as $level)
                    <option value="{{ $level->id }}" @selected(old('level_id', $course?->level_id) == $level->id)>{{ $level->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
            <select name="status" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                <option value="draft" @selected(old('status', $course?->status ?? 'draft') === 'draft')>Draft</option>
                <option value="published" @selected(old('status', $course?->status) === 'published')>Published</option>
                <option value="archived" @selected(old('status', $course?->status) === 'archived')>Archived</option>
            </select>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Price</label>
            <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $course?->price ?? 0) }}" required class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Currency</label>
            <input type="text" name="currency" value="{{ old('currency', $course?->currency ?? 'USD') }}" maxlength="3" required class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subscription Days</label>
            <input type="number" min="1" name="subscription_days" value="{{ old('subscription_days', $course?->subscription_days ?? 120) }}" required class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Duration (days)</label>
        <input type="number" min="1" name="duration_days" value="{{ old('duration_days', $course?->duration_days ?? 120) }}" required class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
    </div>

    <div class="flex items-center gap-2">
        <input type="checkbox" name="is_featured" value="1" id="is_featured" @checked(old('is_featured', $course?->is_featured)) class="rounded border-gray-300 text-indigo-600">
        <label for="is_featured" class="text-sm text-gray-700 dark:text-gray-300">Featured on homepage</label>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Thumbnail</label>
        @if ($course?->thumbnail)
            <img src="{{ $course->thumbnail->resolved_url }}" class="mt-2 h-24 w-24 rounded-lg object-cover">
        @endif
        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Upload a file, or provide an external image URL (Imgur supported).</p>
        <div class="mt-1 grid grid-cols-2 gap-3">
            <input type="file" name="thumbnail_file" accept="image/*" class="text-sm text-gray-600 dark:text-gray-300">
            <input type="url" name="thumbnail_url" placeholder="https://i.imgur.com/..." class="rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
        </div>
        @error('file') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        @error('url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>
</div>
