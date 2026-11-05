<x-layouts.student :title="'Your Result'">
    <div class="mx-auto max-w-3xl text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-900/40">
            <x-icons.medal class="h-8 w-8 text-indigo-600 dark:text-indigo-300" />
        </div>

        <h1 class="mt-4 text-2xl font-bold text-gray-900 dark:text-white">You've been placed into {{ $track->track_code }}</h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            {{ $track->name }} — one payment unlocks this and every track on EnglishBaro, so you can
            progress all the way up at your own pace.
        </p>

        <a href="{{ route('student.intro-video') }}" class="mt-6 inline-flex items-center justify-center rounded-md bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
            Continue
        </a>
    </div>

    @if ($attempt && $attempt->answers->isNotEmpty())
        <div class="mx-auto mt-10 max-w-2xl text-left">
            <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Review your answers</p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                This is read-only — your placement is already set. Only the first placement test decides your level; retaking it here won't change anything.
            </p>

            <div class="mt-4 space-y-3">
                @foreach ($attempt->answers as $answer)
                    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $loop->iteration }}. {{ $answer->question?->question }}</p>
                        <p class="mt-1 flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-300">
                            <x-icons.chevron-right class="h-3.5 w-3.5 flex-none text-gray-400" />
                            {{ $answer->option?->option_text }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</x-layouts.student>
