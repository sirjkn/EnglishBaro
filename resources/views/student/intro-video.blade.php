<x-layouts.student :title="'Intro Video'">
    <div class="mx-auto max-w-3xl">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Welcome to EnglishBaro</h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">A quick introduction before you dive into your courses.</p>

        <div class="mt-6">
            @if ($introVideo)
                <x-video-player :media="$introVideo" />
            @else
                <div class="flex aspect-video items-center justify-center rounded-xl bg-gray-100 text-sm text-gray-400 dark:bg-gray-800">
                    No intro video has been set up yet.
                </div>
            @endif
        </div>

        <a href="{{ route('student.tracks.index') }}" class="mt-6 inline-flex items-center justify-center rounded-md bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
            Get Started
        </a>
    </div>
</x-layouts.student>
