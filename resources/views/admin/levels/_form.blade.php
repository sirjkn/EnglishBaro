<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
    <input type="text" name="name" value="{{ old('name', $level?->name) }}" required class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
    <textarea name="description" rows="3" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">{{ old('description', $level?->description) }}</textarea>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Order</label>
    <input type="number" name="order" value="{{ old('order', $level?->order ?? 0) }}" min="0" required class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
</div>

<div class="flex items-center gap-2">
    <input type="checkbox" name="is_default" value="1" id="is_default" @checked(old('is_default', $level?->is_default)) class="rounded border-gray-300 text-indigo-600">
    <label for="is_default" class="text-sm text-gray-700 dark:text-gray-300">Default level for new students</label>
</div>

<div class="flex items-center gap-2">
    <input type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $level?->is_active ?? true)) class="rounded border-gray-300 text-indigo-600">
    <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300">Active</label>
</div>
