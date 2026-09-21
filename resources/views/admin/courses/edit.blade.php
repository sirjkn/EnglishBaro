<x-layouts.admin :title="'Edit Course'">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Course</h1>

    <div class="mt-6 grid grid-cols-1 gap-8 lg:grid-cols-2">
        <div>
            <form method="POST" action="{{ route('admin.courses.update', $course) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('admin.courses._form', ['course' => $course])

                <button type="submit" class="mt-6 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save Changes</button>
            </form>
        </div>

        <div>
            <h2 class="font-semibold text-gray-900 dark:text-white">Sections &amp; Lessons</h2>

            <div class="mt-4 space-y-4">
                @foreach ($course->sections as $section)
                    <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <p class="font-medium text-gray-900 dark:text-white">{{ $section->title }}</p>
                            <form method="POST" action="{{ route('admin.courses.sections.destroy', [$course, $section]) }}" onsubmit="return confirm('Delete this section and all its lessons?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-500">Delete Section</button>
                            </form>
                        </div>

                        <ul class="mt-3 space-y-2">
                            @foreach ($section->lessons as $lesson)
                                <li class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 text-sm dark:bg-gray-900">
                                    <span class="text-gray-700 dark:text-gray-300">{{ $lesson->title }}</span>
                                    <form method="POST" action="{{ route('admin.courses.lessons.destroy', [$course, $lesson]) }}" onsubmit="return confirm('Delete this lesson?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-500">Delete</button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>

                        <details class="mt-3">
                            <summary class="cursor-pointer text-xs font-medium text-indigo-600">+ Add Lesson</summary>
                            <form method="POST" action="{{ route('admin.courses.lessons.store', [$course, $section]) }}" enctype="multipart/form-data" class="mt-2 space-y-2">
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

            <details class="mt-4 rounded-xl border border-dashed border-gray-300 p-4 dark:border-gray-700">
                <summary class="cursor-pointer text-sm font-medium text-indigo-600">+ Add Section</summary>
                <form method="POST" action="{{ route('admin.courses.sections.store', $course) }}" class="mt-3 space-y-2">
                    @csrf
                    <input type="text" name="title" placeholder="Section title" required class="w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    <textarea name="description" placeholder="Description" rows="2" class="w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white"></textarea>
                    <button type="submit" class="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">Add Section</button>
                </form>
            </details>
        </div>
    </div>
</x-layouts.admin>
