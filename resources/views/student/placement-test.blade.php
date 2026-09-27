<x-layouts.student :title="'Placement Test'">
    <div class="mx-auto max-w-3xl">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Welcome! Let's find your level.</h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Before you can enroll in a track, answer these {{ $questions->count() }} quick questions.
            We'll place you into the track that matches your current English level — A1, A2, B1, or B2 —
            and you'll be able to enroll in that track or any easier one.
        </p>

        <form method="POST" action="{{ route('student.placement-test.store') }}" class="mt-8 space-y-6">
            @csrf

            @foreach ($questions as $question)
                <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                    <p class="font-medium text-gray-900 dark:text-white">{{ $loop->iteration }}. {{ $question->question }}</p>

                    <div class="mt-3 space-y-2">
                        @foreach ($question->options as $option)
                            <label class="flex items-center gap-2 rounded-md border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                                <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}" required class="text-indigo-600">
                                <span class="text-gray-700 dark:text-gray-200">{{ $option->option_text }}</span>
                            </label>
                        @endforeach
                    </div>

                    @error('answers.'.$question->id)
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach

            <button type="submit" class="w-full rounded-md bg-indigo-600 px-4 py-3 text-sm font-semibold text-white hover:bg-indigo-500">
                Submit and See My Placement
            </button>
        </form>
    </div>
</x-layouts.student>
