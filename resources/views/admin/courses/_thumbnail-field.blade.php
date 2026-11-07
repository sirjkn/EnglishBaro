<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Thumbnail</label>
    @if ($course->thumbnail)
        <img src="{{ $course->thumbnail->resolved_url }}" class="mt-2 h-24 w-24 rounded-lg object-cover">
    @endif
    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Upload a file, or provide an external image URL (Imgur supported).</p>
    <div class="mt-1 grid grid-cols-2 gap-3">
        <input type="file" name="thumbnail_file" accept="image/*" form="course-form-{{ $course->id ?? 'new' }}" class="text-sm text-gray-600 dark:text-gray-300">
        <input type="url" name="thumbnail_url" placeholder="https://i.imgur.com/..." form="course-form-{{ $course->id ?? 'new' }}" class="rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
    </div>
</div>
