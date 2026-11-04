<x-layouts.student :title="$meta['title']">
    <div class="max-w-2xl">
        <a href="{{ route('student.more-practice') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
            &larr; Back to More Practice
        </a>

        <div class="mt-4 rounded-xl border border-gray-200 bg-white p-8 text-center dark:border-gray-700 dark:bg-gray-800">
            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-lg bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                <x-dynamic-component :component="'icons.'.$meta['icon']" class="h-6 w-6" />
            </span>

            <h1 class="mt-4 text-xl font-bold text-gray-900 dark:text-white">{{ $meta['title'] }}</h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $meta['description'] }}</p>
            @if ($levelLabel)
                <p class="mt-3 inline-block rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-500 dark:bg-gray-700 dark:text-gray-400">{{ $levelLabel }} level</p>
            @endif

            @if ($questions->isEmpty())
                <p class="mt-6 text-sm font-medium text-gray-700 dark:text-gray-300">
                    We're still building the question set for this practice set — check back soon.
                </p>
            @endif
        </div>

        @if ($questions->isNotEmpty())
            <div x-data="{ answers: {}, checked: false }" class="mt-4 space-y-4">
                @foreach ($questions as $question)
                    <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $loop->iteration }}. {{ $question->question }}</p>
                        <div class="mt-3 space-y-2">
                            @foreach ($question->options as $option)
                                <label class="flex items-center gap-2 rounded-md border border-gray-200 p-2 text-sm dark:border-gray-700"
                                       :class="checked && {{ $option->is_correct ? 'true' : 'false' }} ? 'border-green-400 bg-green-50 dark:bg-green-900/20' : (checked && answers[{{ $question->id }}] === {{ $option->id }} ? 'border-red-400 bg-red-50 dark:bg-red-900/20' : '')">
                                    <input type="radio" name="q{{ $question->id }}" value="{{ $option->id }}"
                                           x-model.number="answers[{{ $question->id }}]" :disabled="checked"
                                           @change="answers['correct_{{ $question->id }}'] = {{ $option->is_correct ? 'true' : 'false' }}">
                                    {{ $option->option_text }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                    <button type="button" @click="checked = true" x-show="!checked"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                        Check my answers
                    </button>
                    <p x-show="checked" class="text-sm font-semibold text-gray-900 dark:text-white">
                        You got <span x-text="Object.keys(answers).filter(k => k.startsWith('correct_') && answers[k]).length"></span> out of {{ $questions->count() }} correct.
                    </p>
                    <button type="button" @click="answers = {}; checked = false" x-show="checked"
                            class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                        Try again
                    </button>
                </div>
            </div>
        @endif
    </div>
</x-layouts.student>
