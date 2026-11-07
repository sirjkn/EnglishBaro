<x-layouts.admin :title="'More Practice'">
    <div x-data="{ addOpen: {{ $errors->any() ? 'true' : 'false' }} }">
    <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="flex flex-wrap items-start justify-between gap-3 bg-indigo-600 px-4 py-3 dark:bg-indigo-800">
            <div>
                <h1 class="text-sm font-bold text-white">More Practice</h1>
                <p class="mt-1 max-w-2xl text-xs text-indigo-100">
                    Multiple-choice questions students see on the More Practice page. Pick a track to restrict a question
                    to that level, or leave it blank to show it to every student.
                </p>
            </div>
            <button type="button" @click="addOpen = true; $nextTick(() => $refs.addQuestionForm.scrollIntoView({ behavior: 'smooth' }))"
                    class="flex-none rounded-md border border-green-600 bg-green-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-400">
                + Add Question
            </button>
        </div>

        <div class="flex flex-wrap gap-2 border-t border-gray-200 p-3 dark:border-gray-700">
            @foreach ($categories as $slug => $meta)
                <a href="{{ route('admin.practice-questions.index', ['category' => $slug]) }}"
                   class="rounded-md border px-3 py-1.5 text-xs font-semibold {{ $category === $slug ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-gray-300 bg-gray-100 text-gray-600 hover:bg-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                    {{ $meta['title'] }}
                </a>
            @endforeach
        </div>
    </div>

    <div class="mt-6 space-y-4">
        @forelse ($questions as $question)
            <div class="rounded-xl border border-black/10 bg-white p-5 dark:border-white/10 dark:bg-gray-800">
                <form method="POST" action="{{ route('admin.practice-questions.update', $question) }}" class="space-y-3">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="category" value="{{ $category }}">

                    <div class="flex flex-wrap items-end gap-3">
                        <div class="min-w-0 flex-1">
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Question {{ $loop->iteration }}</label>
                            <input type="text" name="question" value="{{ $question->question }}" required
                                   class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                        </div>
                        <div class="w-40">
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Track (optional)</label>
                            <select name="track_id" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                                <option value="">All tracks</option>
                                @foreach ($tracks as $track)
                                    <option value="{{ $track->id }}" @selected($question->track_id === $track->id)>{{ $track->track_code }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach ($question->options as $index => $option)
                            <label class="flex items-center gap-2 rounded-md border border-gray-200 p-2 dark:border-gray-700">
                                <input type="radio" name="correct" value="{{ $index }}" @checked($option->is_correct) required>
                                <input type="text" name="options[]" value="{{ $option->option_text }}" required
                                       class="w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                            </label>
                        @endforeach
                    </div>

                    <button type="submit" class="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">Save</button>
                </form>
                <form method="POST" action="{{ route('admin.practice-questions.destroy', $question) }}" class="mt-2" onsubmit="return confirm('Delete this question?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-500">Delete Question</button>
                </form>
            </div>
        @empty
            <x-empty-state message="No {{ $categories[$category]['title'] }} questions yet." />
        @endforelse
    </div>

    <div x-ref="addQuestionForm" x-show="addOpen" x-cloak class="mt-6 rounded-xl border border-dashed border-gray-300 p-4 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-indigo-600">Add Question to {{ $categories[$category]['title'] }}</p>
            <button type="button" @click="addOpen = false" class="text-xs text-gray-400 hover:text-gray-600">Cancel</button>
        </div>
        <form method="POST" action="{{ route('admin.practice-questions.store') }}" class="mt-3 space-y-3">
            @csrf
            <input type="hidden" name="category" value="{{ $category }}">

            <div class="flex flex-wrap items-end gap-3">
                <div class="min-w-0 flex-1">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Question</label>
                    <input type="text" name="question" value="{{ old('question') }}" required placeholder="e.g. She ___ to work every day."
                           class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    @error('question') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="w-40">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Track (optional)</label>
                    <select name="track_id" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                        <option value="">All tracks</option>
                        @foreach ($tracks as $track)
                            <option value="{{ $track->id }}" @selected(old('track_id') == $track->id)>{{ $track->track_code }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                @for ($index = 0; $index < 4; $index++)
                    <label class="flex items-center gap-2 rounded-md border border-gray-200 p-2 dark:border-gray-700">
                        <input type="radio" name="correct" value="{{ $index }}" @checked(old('correct') == $index) required>
                        <input type="text" name="options[]" value="{{ old('options.'.$index) }}" required placeholder="Option {{ $index + 1 }}"
                               class="w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    </label>
                @endfor
            </div>
            @error('options') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            <p class="text-xs text-gray-400">Select the radio button next to the correct answer.</p>

            <button type="submit" class="rounded-md bg-gradient-to-r from-indigo-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:from-indigo-500 hover:to-indigo-400">
                Add Question
            </button>
        </form>
    </div>
    </div>
</x-layouts.admin>
