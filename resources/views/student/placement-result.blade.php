<x-layouts.student :title="'Your Result'">
    <div class="mx-auto max-w-3xl text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-900/40">
            <x-icons.medal class="h-8 w-8 text-indigo-600 dark:text-indigo-300" />
        </div>

        <h1 class="mt-4 text-2xl font-bold text-gray-900 dark:text-white">You've been placed into {{ $attempt->track->track_code }}</h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            {{ $attempt->track->title }} — one payment unlocks this and every track on EnglishBaro, so you can
            progress all the way up at your own pace.
        </p>

        <a href="{{ route('student.intro-video') }}" class="mt-6 inline-flex items-center justify-center rounded-md bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
            Continue
        </a>
    </div>
</x-layouts.student>
