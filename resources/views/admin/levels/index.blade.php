<x-layouts.admin :title="'Levels - '.$track->track_code">
    <div>
        <a href="{{ route('admin.tracks.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">&larr; Tracks</a>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $track->track_code }} &middot; Levels</h1>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-4">
        {{-- 1/4: level list --}}
        <div class="lg:col-span-1">
            <details class="rounded-xl border border-dashed border-gray-300 p-4 dark:border-gray-700">
                <summary class="cursor-pointer text-sm font-medium text-indigo-600">+ Add Level</summary>
                <form method="POST" action="{{ route('admin.tracks.levels.store', $track) }}" class="mt-3 space-y-2">
                    @csrf
                    <input type="number" name="number" min="1" placeholder="Level number" required class="w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    <input type="text" name="title" placeholder="Level title" required class="w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    <button type="submit" class="w-full rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500">Add Level</button>
                </form>
                @error('number') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </details>

            <div class="mt-4 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 text-xs dark:divide-gray-700">
                    <thead>
                        <tr class="bg-indigo-600 text-left text-[11px] uppercase text-white dark:bg-indigo-800">
                            <th class="px-3 py-2">Level</th>
                            <th class="px-3 py-2">Sections</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-indigo-100 dark:divide-gray-800">
                        @forelse ($levels as $level)
                            <tr class="{{ $openLevel?->id === $level->id ? 'bg-indigo-100 dark:bg-indigo-900/40' : 'odd:bg-white even:bg-indigo-50 hover:bg-indigo-100 dark:odd:bg-gray-800 dark:even:bg-gray-900 dark:hover:bg-gray-700' }}">
                                <td colspan="2" class="p-0">
                                    <a
                                        href="{{ route('admin.tracks.levels.index', [$track, 'open' => $level->id]) }}"
                                        class="grid grid-cols-[1fr_auto] items-center gap-2 px-3 py-2.5"
                                    >
                                        <span class="truncate font-semibold text-gray-900 dark:text-white">#{{ $level->number }} &middot; {{ $level->title }}</span>
                                        <span class="text-gray-500 dark:text-gray-400">{{ $level->sections_count }}</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="px-3 py-8 text-center text-gray-400">No levels yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $levels->withQueryString()->links() }}</div>
        </div>

        {{-- 3/4: selected level's editor --}}
        <div class="lg:col-span-3">
            @if ($openLevel)
                @php $level = $openLevel; @endphp
                <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                    <form method="POST" action="{{ route('admin.tracks.levels.update', [$track, $level]) }}" class="flex max-w-xl items-end gap-3">
                        @csrf
                        @method('PUT')
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Level #{{ $level->number }} Title</label>
                            <input type="text" name="title" value="{{ old('title', $level->title) }}" required class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                            @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save</button>
                        <x-confirm-delete-form
                            :action="route('admin.tracks.levels.destroy', [$track, $level])"
                            label="Delete Level"
                            :title="'Delete level '.$level->number.'?'"
                            message="This removes the level and all its sections and lessons. This cannot be undone."
                            class="rounded-md border border-red-300 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-900/30"
                        />
                    </form>

                    <h2 class="mt-8 font-semibold text-gray-900 dark:text-white">Sections &amp; Lessons</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Every level has the same four sections. Add lessons to each one.</p>

                    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
                        @foreach ($level->sections as $section)
                            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                                <p class="font-medium text-gray-900 dark:text-white">{{ $section->title }}</p>

                                <ul class="mt-3 space-y-2">
                                    @forelse ($section->lessons as $lesson)
                                        <li class="rounded-lg bg-gray-50 px-3 py-2 text-sm dark:bg-gray-900" x-data="{ lessonModalOpen: window.location.hash === '#lesson-{{ $lesson->id }}' }" id="lesson-{{ $lesson->id }}">
                                            <div class="flex items-center justify-between">
                                                <span class="text-gray-700 dark:text-gray-300">{{ $lesson->title }}</span>
                                                <span class="flex items-center gap-3">
                                                    <button type="button" class="text-xs font-medium text-indigo-600 hover:text-indigo-500" @click="lessonModalOpen = true">Edit</button>
                                                    <x-confirm-delete-form
                                                        :action="route('admin.tracks.lessons.destroy', [$track, $level, $lesson])"
                                                        label="Delete"
                                                        :title="'Delete lesson '.$lesson->title.'?'"
                                                        message="This removes the lesson and any progress students have made on it. This cannot be undone."
                                                        class="text-xs font-medium text-red-600 hover:text-red-500"
                                                    />
                                                </span>
                                            </div>

                                            <template x-teleport="body">
                                            <div x-show="lessonModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
                                                <div class="fixed inset-0 bg-black/50" @click="lessonModalOpen = false"></div>

                                                <div class="relative max-h-[85vh] w-full max-w-lg overflow-y-auto rounded-xl border border-black/10 bg-white p-5 shadow-xl dark:border-white/10 dark:bg-gray-800">
                                                <div class="flex items-center justify-between">
                                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Edit Lesson</h3>
                                                    <button type="button" @click="lessonModalOpen = false" class="rounded-md bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700">Close</button>
                                                </div>

                                                <form method="POST" action="{{ route('admin.tracks.lessons.update', [$track, $level, $lesson]) }}" enctype="multipart/form-data" class="mt-4 space-y-2" x-data="lessonMediaUploader({{ $lesson->id }})">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="open" value="lesson-{{ $lesson->id }}">
                                                    <input type="text" name="title" value="{{ $lesson->title }}" required class="w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                                                    <textarea name="description" placeholder="Description" rows="2" class="w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">{{ $lesson->description }}</textarea>

                                                    <x-lesson-media-fields :lesson="$lesson" />

                                                    <label class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                                                        <input type="checkbox" name="is_preview" value="1" @checked($lesson->is_preview) class="rounded border-gray-300 text-indigo-600">
                                                        Free preview lesson
                                                    </label>
                                                    <button type="submit" :disabled="isUploading" :class="isUploading ? 'cursor-not-allowed bg-gray-300 dark:bg-gray-600' : 'bg-indigo-600 hover:bg-indigo-500'" class="rounded-md px-3 py-1.5 text-xs font-semibold text-white" x-text="isUploading ? 'Uploading…' : 'Save'"></button>
                                                </form>

                                                <div class="mt-3 border-t border-gray-200 pt-3 dark:border-gray-700">
                                                    <p class="flex items-center gap-1.5 text-xs font-semibold uppercase text-indigo-600 dark:text-indigo-400">
                                                        <x-icons.medal class="h-3.5 w-3.5" /> Activity Questions
                                                    </p>

                                                    <form method="POST" action="{{ route('admin.tracks.lessons.activity.update-title', [$track, $level, $lesson]) }}" class="mt-2 flex items-center gap-2">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="open" value="lesson-{{ $lesson->id }}">
                                                        <input type="text" name="title" value="{{ $lesson->activity->title ?? $lesson->title.' Quiz' }}" placeholder="Quiz title" class="w-full rounded-md border-gray-300 text-xs dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                                                        <button type="submit" class="shrink-0 rounded-md border border-gray-300 px-2 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">Save Title</button>
                                                    </form>

                                                    <div class="mt-2 space-y-2">
                                                        @forelse ($lesson->activity?->questions ?? [] as $question)
                                                            <details class="rounded-lg bg-gray-50 p-2 dark:bg-gray-900" id="question-{{ $question->id }}">
                                                                <summary class="cursor-pointer text-xs text-gray-700 dark:text-gray-300">{{ $question->question }}</summary>
                                                                <form method="POST" action="{{ route('admin.tracks.lessons.activity.questions.update', [$track, $level, $lesson, $question]) }}" class="mt-2 space-y-2"
                                                                      x-data="{
                                                                          type: '{{ $question->type }}',
                                                                          allowMultiple: {{ $question->allow_multiple ? 'true' : 'false' }},
                                                                          options: {{ Illuminate\Support\Js::from($question->options->pluck('option_text')->values()->all() ?: ['', '']) }},
                                                                          correctSingle: {{ (int) $question->options->search(fn ($o) => $o->is_correct) }},
                                                                          correctMulti: {{ Illuminate\Support\Js::from($question->options->filter(fn ($o) => $o->is_correct)->keys()->values()->all()) }},
                                                                      }">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <input type="hidden" name="open" value="question-{{ $question->id }}">
                                                                    <textarea name="question" rows="2" required class="w-full rounded-md border-gray-300 text-xs dark:border-gray-600 dark:bg-gray-900 dark:text-white">{{ $question->question }}</textarea>

                                                                    <select name="type" x-model="type" class="w-full rounded-md border-gray-300 text-xs dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                                                                        <option value="multiple_choice" @selected($question->type === 'multiple_choice')>Multiple Choice</option>
                                                                        <option value="fill_blank" @selected($question->type === 'fill_blank')>Fill the Blank</option>
                                                                        <option value="short_answer" @selected($question->type === 'short_answer')>Short Answer</option>
                                                                    </select>

                                                                    <div x-show="type === 'multiple_choice'" class="space-y-1.5">
                                                                        <label class="flex items-center gap-1.5 text-[11px] text-gray-600 dark:text-gray-300">
                                                                            <input type="checkbox" name="allow_multiple" value="1" x-model="allowMultiple" @change="correctMulti = []; correctSingle = 0" class="rounded text-indigo-600">
                                                                            Allow multiple correct answers
                                                                        </label>
                                                                        <p class="text-[11px] font-medium text-gray-500 dark:text-gray-400">Select the circle/checkbox next to the correct choice — it's graded automatically.</p>

                                                                        <template x-for="(opt, index) in options" :key="index">
                                                                            <div class="flex items-center gap-2 rounded-md border px-2 py-1" :class="(!allowMultiple && correctSingle === index) || (allowMultiple && correctMulti.includes(index)) ? 'border-green-400 bg-green-50 dark:bg-green-900/20' : 'border-transparent'">
                                                                                <template x-if="!allowMultiple">
                                                                                    <input type="radio" :value="index" x-model.number="correctSingle" class="text-indigo-600">
                                                                                </template>
                                                                                <template x-if="allowMultiple">
                                                                                    <input type="checkbox" :checked="correctMulti.includes(index)" @change="$event.target.checked ? correctMulti.push(index) : correctMulti = correctMulti.filter(i => i !== index)" class="text-indigo-600">
                                                                                </template>
                                                                                <input type="text" :name="`options[${index}]`" x-model="options[index]" :placeholder="`Option ${index + 1}`" class="w-full rounded-md border-gray-300 text-xs dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                                                                                <button type="button" x-show="options.length > 2" @click="options.splice(index, 1); correctMulti = correctMulti.filter(i => i !== index).map(i => i > index ? i - 1 : i); if (correctSingle >= index) correctSingle = Math.max(0, correctSingle - 1);" class="shrink-0 text-red-500 hover:text-red-600">&times;</button>
                                                                            </div>
                                                                        </template>

                                                                        <button type="button" x-show="options.length < 10" @click="options.push('')" class="text-xs font-medium text-indigo-600 hover:text-indigo-500">+ Add Choice</button>

                                                                        <input type="hidden" name="correct_option" :value="correctSingle" x-bind:disabled="allowMultiple">
                                                                        <template x-for="i in (allowMultiple ? correctMulti : [])" :key="i">
                                                                            <input type="hidden" name="correct_options[]" :value="i">
                                                                        </template>
                                                                    </div>

                                                                    <div x-show="type === 'short_answer' || type === 'fill_blank'">
                                                                        <input type="text" name="correct_short_answer" value="{{ $question->correct_short_answer }}" placeholder="e.g. blue, azure, sky blue" class="w-full rounded-md border-gray-300 text-xs dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                                                                        <p class="mt-1 text-[11px] text-gray-400">Graded automatically. If more than one answer is acceptable, separate them with commas.</p>
                                                                    </div>

                                                                    <div class="flex items-center justify-between">
                                                                        <button type="submit" class="rounded-md bg-indigo-600 px-3 py-1 text-xs font-semibold text-white hover:bg-indigo-500">Save</button>
                                                                    </div>
                                                                </form>
                                                                <form method="POST" action="{{ route('admin.tracks.lessons.activity.questions.destroy', [$track, $level, $lesson, $question]) }}" class="mt-1" onsubmit="return confirm('Delete this question?');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-500">Delete Question</button>
                                                                </form>
                                                            </details>
                                                        @empty
                                                            <p class="text-xs text-gray-400">No activity questions yet.</p>
                                                        @endforelse
                                                    </div>

                                                    <details class="mt-2" id="add-question-{{ $lesson->id }}">
                                                        <summary class="cursor-pointer text-xs font-medium text-indigo-600">+ Add Activity Question</summary>
                                                        <form method="POST" action="{{ route('admin.tracks.lessons.activity.questions.store', [$track, $level, $lesson]) }}" class="mt-2 space-y-2"
                                                              x-data="{ type: 'multiple_choice', allowMultiple: false, options: ['', '', '', ''], correctSingle: 0, correctMulti: [] }">
                                                            @csrf
                                                            <input type="hidden" name="open" value="add-question-{{ $lesson->id }}">
                                                            <textarea name="question" rows="2" required placeholder="Question text" class="w-full rounded-md border-gray-300 text-xs dark:border-gray-600 dark:bg-gray-900 dark:text-white"></textarea>

                                                            <select name="type" x-model="type" class="w-full rounded-md border-gray-300 text-xs dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                                                                <option value="multiple_choice">Multiple Choice</option>
                                                                <option value="fill_blank">Fill the Blank</option>
                                                                <option value="short_answer">Short Answer</option>
                                                            </select>

                                                            <div x-show="type === 'multiple_choice'" class="space-y-1.5">
                                                                <label class="flex items-center gap-1.5 text-[11px] text-gray-600 dark:text-gray-300">
                                                                    <input type="checkbox" name="allow_multiple" value="1" x-model="allowMultiple" @change="correctMulti = []; correctSingle = 0" class="rounded text-indigo-600">
                                                                    Allow multiple correct answers
                                                                </label>
                                                                <p class="text-[11px] font-medium text-gray-500 dark:text-gray-400">Select the circle/checkbox next to the correct choice — it's graded automatically.</p>

                                                                <template x-for="(opt, index) in options" :key="index">
                                                                    <div class="flex items-center gap-2 rounded-md border px-2 py-1" :class="(!allowMultiple && correctSingle === index) || (allowMultiple && correctMulti.includes(index)) ? 'border-green-400 bg-green-50 dark:bg-green-900/20' : 'border-transparent'">
                                                                        <template x-if="!allowMultiple">
                                                                            <input type="radio" :value="index" x-model.number="correctSingle" class="text-indigo-600">
                                                                        </template>
                                                                        <template x-if="allowMultiple">
                                                                            <input type="checkbox" :checked="correctMulti.includes(index)" @change="$event.target.checked ? correctMulti.push(index) : correctMulti = correctMulti.filter(i => i !== index)" class="text-indigo-600">
                                                                        </template>
                                                                        <input type="text" :name="`options[${index}]`" x-model="options[index]" :placeholder="`Option ${index + 1}`" class="w-full rounded-md border-gray-300 text-xs dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                                                                        <button type="button" x-show="options.length > 2" @click="options.splice(index, 1); correctMulti = correctMulti.filter(i => i !== index).map(i => i > index ? i - 1 : i); if (correctSingle >= index) correctSingle = Math.max(0, correctSingle - 1);" class="shrink-0 text-red-500 hover:text-red-600">&times;</button>
                                                                    </div>
                                                                </template>

                                                                <button type="button" x-show="options.length < 10" @click="options.push('')" class="text-xs font-medium text-indigo-600 hover:text-indigo-500">+ Add Choice</button>

                                                                <input type="hidden" name="correct_option" :value="correctSingle" x-bind:disabled="allowMultiple">
                                                                <template x-for="i in (allowMultiple ? correctMulti : [])" :key="i">
                                                                    <input type="hidden" name="correct_options[]" :value="i">
                                                                </template>
                                                            </div>

                                                            <div x-show="type === 'short_answer' || type === 'fill_blank'">
                                                                <input type="text" name="correct_short_answer" placeholder="e.g. blue, azure, sky blue" class="w-full rounded-md border-gray-300 text-xs dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                                                                <p class="mt-1 text-[11px] text-gray-400">Graded automatically. If more than one answer is acceptable, separate them with commas.</p>
                                                            </div>

                                                            <button type="submit" class="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">Add Question</button>
                                                        </form>
                                                    </details>
                                                </div>
                                                </div>
                                            </div>
                                            </template>
                                        </li>
                                    @empty
                                        <li class="px-3 py-2 text-sm text-gray-400">No lessons yet.</li>
                                    @endforelse
                                </ul>

                                <div x-data="{ open: window.location.hash === '#add-lesson-{{ $section->id }}' }" id="add-lesson-{{ $section->id }}">
                                    <button type="button" @click="open = true" class="text-xs font-medium text-indigo-600 hover:text-indigo-500">+ Add Lesson</button>

                                    <template x-teleport="body">
                                        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
                                            <div class="fixed inset-0 bg-black/50" @click="open = false"></div>

                                            <div class="relative max-h-[85vh] w-full max-w-lg overflow-y-auto rounded-xl border border-black/10 bg-white p-5 shadow-xl dark:border-white/10 dark:bg-gray-800">
                                                <div class="flex items-center justify-between">
                                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Add Lesson &mdash; {{ $section->title }}</h3>
                                                    <button type="button" @click="open = false" class="rounded-md bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700">Close</button>
                                                </div>

                                                <form method="POST" action="{{ route('admin.tracks.lessons.store', [$track, $level, $section]) }}" enctype="multipart/form-data" class="mt-4 space-y-2" x-data="lessonMediaUploader()">
                                                    @csrf
                                                    <input type="hidden" name="open" value="add-lesson-{{ $section->id }}">
                                                    <input type="text" name="title" placeholder="Lesson title" required class="w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                                                    <textarea name="description" placeholder="Description" rows="2" class="w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white"></textarea>

                                                    <x-lesson-media-fields />

                                                    <label class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                                                        <input type="checkbox" name="is_preview" value="1" class="rounded border-gray-300 text-indigo-600">
                                                        Free preview lesson
                                                    </label>
                                                    <button type="submit" :disabled="isUploading" :class="isUploading ? 'cursor-not-allowed bg-gray-300 dark:bg-gray-600' : 'bg-indigo-600 hover:bg-indigo-500'" class="rounded-md px-3 py-1.5 text-xs font-semibold text-white" x-text="isUploading ? 'Uploading…' : 'Add Lesson'"></button>
                                                </form>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="flex h-48 items-center justify-center rounded-xl border border-dashed border-gray-300 px-4 text-center text-sm text-gray-400 dark:border-gray-600">
                    Click a level on the left to edit its title, sections, and lessons.
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
