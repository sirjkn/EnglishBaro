<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
        <input type="text" name="name" value="{{ old('name', $testimonial?->name) }}" required class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role Label</label>
        <input type="text" name="role_label" value="{{ old('role_label', $testimonial?->role_label) }}" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Content</label>
        <textarea name="content" rows="4" required class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">{{ old('content', $testimonial?->content) }}</textarea>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Rating (1-5)</label>
            <input type="number" name="rating" min="1" max="5" value="{{ old('rating', $testimonial?->rating ?? 5) }}" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Order</label>
            <input type="number" name="order" min="0" value="{{ old('order', $testimonial?->order ?? 0) }}" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Avatar</label>
        @if ($testimonial?->avatar)
            <img src="{{ $testimonial->avatar->resolved_url }}" class="mt-2 h-16 w-16 rounded-full object-cover">
        @endif
        <div class="mt-1 grid grid-cols-2 gap-3">
            <input type="file" name="avatar_file" accept="image/*" class="text-sm text-gray-600 dark:text-gray-300">
            <input type="url" name="avatar_url" placeholder="https://i.imgur.com/..." class="rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
        </div>
    </div>

    <div class="flex items-center gap-2">
        <input type="checkbox" name="is_published" value="1" id="is_published" @checked(old('is_published', $testimonial?->is_published)) class="rounded border-gray-300 text-indigo-600">
        <label for="is_published" class="text-sm text-gray-700 dark:text-gray-300">Published</label>
    </div>
</div>
