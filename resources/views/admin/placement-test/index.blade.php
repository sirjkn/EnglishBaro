<x-layouts.admin :title="'Placement Test'">
    <div x-data="{ addOpen: {{ $errors->any() ? 'true' : 'false' }} }">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Placement Test</h1>
            <p class="mt-1 max-w-2xl text-sm text-gray-600 dark:text-gray-400">
                New students answer these questions before their first enrollment. Whichever track's answers they pick
                most often becomes their placement. Each question needs one answer per track, ordered from easiest to hardest.
            </p>
        </div>
        <button type="button" @click="addOpen = true; $nextTick(() => $refs.addQuestionForm.scrollIntoView({ behavior: 'smooth' }))"
                class="rounded-md bg-gradient-to-r from-indigo-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:from-indigo-500 hover:to-indigo-400">
            + Add Question
        </button>
    </div>

    <div class="mt-6 space-y-4">
        @forelse ($questions as $question)
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                <form method="POST" action="{{ route('admin.placement-test.update', $question) }}" class="space-y-3">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Question {{ $loop->iteration }}</label>
                        <input type="text" name="question" value="{{ old('question', $question->question) }}" required
                               class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($question->options as $option)
                            <div>
                                <label class="block text-xs font-medium text-indigo-600 dark:text-indigo-400">{{ $option->track->track_code }} answer</label>
                                <input type="text" name="answers[{{ $option->track_id }}]" value="{{ old('answers.'.$option->track_id, $option->option_text) }}" required
                                       class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                            </div>
                        @endforeach
                    </div>

                    <button type="submit" class="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">Save</button>
                </form>
                <form method="POST" action="{{ route('admin.placement-test.destroy', $question) }}" class="mt-2" onsubmit="return confirm('Delete this question?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-500">Delete Question</button>
                </form>
            </div>
        @empty
            <x-empty-state message="No placement test questions yet." />
        @endforelse
    </div>

    <div x-ref="addQuestionForm" x-show="addOpen" x-cloak class="mt-6 rounded-xl border border-dashed border-gray-300 p-4 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-indigo-600">Add Question</p>
            <button type="button" @click="addOpen = false" class="text-xs text-gray-400 hover:text-gray-600">Cancel</button>
        </div>
        <form method="POST" action="{{ route('admin.placement-test.store') }}" class="mt-3 space-y-3">
            @csrf

            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Question</label>
                <input type="text" name="question" value="{{ old('question') }}" required placeholder="e.g. Choose the correct sentence."
                       class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                @error('question') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($tracks as $track)
                    <div>
                        <label class="block text-xs font-medium text-indigo-600 dark:text-indigo-400">{{ $track->track_code }} answer</label>
                        <input type="text" name="answers[{{ $track->id }}]" value="{{ old('answers.'.$track->id) }}" required
                               class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    </div>
                @endforeach
            </div>
            @error('answers') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

            <button type="submit" class="rounded-md bg-gradient-to-r from-indigo-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:from-indigo-500 hover:to-indigo-400">
                Add Question
            </button>
        </form>
    </div>
    </div>
</x-layouts.admin>
