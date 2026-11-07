<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Course Code</label>
    @if ($creating ?? false)
        <input type="text" name="course_code" value="{{ old('course_code') }}" placeholder="e.g. SPA" maxlength="10"
               class="mt-1 w-full rounded-md border-gray-300 font-mono text-sm uppercase dark:border-gray-600 dark:bg-gray-900 dark:text-white">
        <p class="mt-1 text-xs text-gray-400">A short unique code (letters/numbers/dashes only), e.g. ENG, SPA.</p>
        @error('course_code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    @else
        <input type="text" value="{{ $course->course_code }}" disabled class="mt-1 w-full rounded-md border-gray-200 bg-gray-100 font-mono text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
        <p class="mt-1 text-xs text-gray-400">The course code can't be changed after creation.</p>
    @endif
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
    <input type="text" name="name" value="{{ old('name', $course->name) }}" required class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
</div>
