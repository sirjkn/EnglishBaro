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
                        <li class="rounded-lg bg-gray-50 px-3 py-2 text-sm dark:bg-gray-900">
                            <details>
                                <summary class="flex list-none items-center justify-between" onclick="event.preventDefault()">
                                    <span class="text-gray-700 dark:text-gray-300">{{ $lesson->title }}</span>
                                    <span class="flex items-center gap-3">
                                        <button type="button" class="text-xs font-medium text-indigo-600 hover:text-indigo-500" onclick="this.closest('details').open = !this.closest('details').open">Edit</button>
                                        <x-confirm-delete-form
                                            :action="route('admin.tracks.lessons.destroy', [$track, $level, $lesson])"
                                            label="Delete"
                                            :title="'Delete lesson '.$lesson->title.'?'"
                                            message="This removes the lesson and any progress students have made on it. This cannot be undone."
                                            class="text-xs font-medium text-red-600 hover:text-red-500"
                                        />
                                    </span>
                                </summary>

                                <form method="POST" action="{{ route('admin.tracks.lessons.update', [$track, $level, $lesson]) }}" enctype="multipart/form-data" class="mt-2 space-y-2">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="title" value="{{ $lesson->title }}" required class="w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                                    <textarea name="description" placeholder="Description" rows="2" class="w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">{{ $lesson->description }}</textarea>
                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="file" name="video_file" accept="video/*" class="text-xs text-gray-600 dark:text-gray-300">
                                        <input type="url" name="video_url" placeholder="Replace video URL" class="rounded-md border-gray-300 text-xs dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                                    </div>
                                    <label class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                                        <input type="checkbox" name="is_preview" value="1" @checked($lesson->is_preview) class="rounded border-gray-300 text-indigo-600">
                                        Free preview lesson
                                    </label>
                                    <button type="submit" class="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">Save</button>
                                </form>
                            </details>
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

                <div class="mt-4 border-t border-gray-200 pt-3 dark:border-gray-700">
                    <p class="flex items-center gap-1.5 text-xs font-semibold uppercase text-indigo-600 dark:text-indigo-400">
                        <x-icons.medal class="h-3.5 w-3.5" /> Activity Questions
                    </p>

                    <div class="mt-2 space-y-2">
                        @forelse ($section->activity?->questions ?? [] as $question)
                            <details class="rounded-lg bg-gray-50 p-2 dark:bg-gray-900">
                                <summary class="cursor-pointer text-xs text-gray-700 dark:text-gray-300">{{ $question->question }}</summary>
                                <form method="POST" action="{{ route('admin.tracks.sections.activity.questions.update', [$track, $level, $section, $question]) }}" class="mt-2 space-y-2" x-data="{ type: '{{ $question->type }}' }">
                                    @csrf
                                    @method('PUT')
                                    <textarea name="question" rows="2" required class="w-full rounded-md border-gray-300 text-xs dark:border-gray-600 dark:bg-gray-900 dark:text-white">{{ $question->question }}</textarea>

                                    <select name="type" x-model="type" class="w-full rounded-md border-gray-300 text-xs dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                                        <option value="multiple_choice" @selected($question->type === 'multiple_choice')>Multiple Choice</option>
                                        <option value="short_answer" @selected($question->type === 'short_answer')>Short Answer</option>
                                    </select>

                                    <div x-show="type === 'multiple_choice'" class="space-y-1.5">
                                        @for ($i = 0; $i < 4; $i++)
                                            <div class="flex items-center gap-2">
                                                <input type="radio" name="correct_option" value="{{ $i }}" @checked(optional($question->options[$i] ?? null)->is_correct) class="text-indigo-600">
                                                <input type="text" name="options[{{ $i }}]" value="{{ $question->options[$i]->option_text ?? '' }}" placeholder="Option {{ $i + 1 }}" class="w-full rounded-md border-gray-300 text-xs dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                                            </div>
                                        @endfor
                                    </div>

                                    <input type="text" name="correct_short_answer" x-show="type === 'short_answer'" value="{{ $question->correct_short_answer }}" placeholder="Correct answer" class="w-full rounded-md border-gray-300 text-xs dark:border-gray-600 dark:bg-gray-900 dark:text-white">

                                    <div class="flex items-center justify-between">
                                        <button type="submit" class="rounded-md bg-indigo-600 px-3 py-1 text-xs font-semibold text-white hover:bg-indigo-500">Save</button>
                                    </div>
                                </form>
                                <form method="POST" action="{{ route('admin.tracks.sections.activity.questions.destroy', [$track, $level, $section, $question]) }}" class="mt-1" onsubmit="return confirm('Delete this question?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-500">Delete Question</button>
                                </form>
                            </details>
                        @empty
                            <p class="text-xs text-gray-400">No activity questions yet.</p>
                        @endforelse
                    </div>

                    <details class="mt-2">
                        <summary class="cursor-pointer text-xs font-medium text-indigo-600">+ Add Activity Question</summary>
                        <form method="POST" action="{{ route('admin.tracks.sections.activity.questions.store', [$track, $level, $section]) }}" class="mt-2 space-y-2" x-data="{ type: 'multiple_choice' }">
                            @csrf
                            <textarea name="question" rows="2" required placeholder="Question text" class="w-full rounded-md border-gray-300 text-xs dark:border-gray-600 dark:bg-gray-900 dark:text-white"></textarea>

                            <select name="type" x-model="type" class="w-full rounded-md border-gray-300 text-xs dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                                <option value="multiple_choice">Multiple Choice</option>
                                <option value="short_answer">Short Answer</option>
                            </select>

                            <div x-show="type === 'multiple_choice'" class="space-y-1.5">
                                @for ($i = 0; $i < 4; $i++)
                                    <div class="flex items-center gap-2">
                                        <input type="radio" name="correct_option" value="{{ $i }}" class="text-indigo-600">
                                        <input type="text" name="options[{{ $i }}]" placeholder="Option {{ $i + 1 }}" class="w-full rounded-md border-gray-300 text-xs dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                                    </div>
                                @endfor
                            </div>

                            <input type="text" name="correct_short_answer" x-show="type === 'short_answer'" placeholder="Correct answer" class="w-full rounded-md border-gray-300 text-xs dark:border-gray-600 dark:bg-gray-900 dark:text-white">

                            <button type="submit" class="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">Add Question</button>
                        </form>
                    </details>
                </div>
            </div>
        @endforeach
    </div>
</x-layouts.admin>
