<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Thumbnail</label>
    @php $formId = ($standalone ?? false) ? 'form="course-form-'.$course->id.'"' : ''; @endphp
    <div class="mt-2 flex items-center gap-4">
        @if ($course->thumbnail)
            <img src="{{ $course->thumbnail->resolved_url }}" class="h-24 w-24 flex-none rounded-lg object-cover">
        @else
            <div class="flex h-24 w-24 flex-none items-center justify-center rounded-lg bg-gray-100 text-gray-400 dark:bg-gray-800">
                <x-icons.photo class="h-8 w-8" />
            </div>
        @endif
        <div class="flex-1 space-y-2">
            <input type="file" name="thumbnail_file" accept="image/*" {!! $formId !!} class="block text-sm text-gray-600 dark:text-gray-300">
            <input type="url" name="thumbnail_url" placeholder="https://i.imgur.com/..." {!! $formId !!} class="block w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
        </div>
    </div>
    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Upload a file, or provide an external image URL (Imgur supported).</p>
</div>
