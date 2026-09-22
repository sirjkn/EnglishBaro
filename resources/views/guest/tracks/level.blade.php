<x-layouts.site :title="$track->track_code.' '.$level->title">
    <div class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
        <a href="{{ route('tracks.show', $track) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
            &larr; Back to Track {{ $track->track_code }}
        </a>

        <h1 class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">{{ $level->title }}</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $track->track_code }} &middot; {{ $track->name }}</p>

        <div class="mt-8 space-y-4">
            @foreach ($level->sections as $section)
                <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                    <h2 class="font-medium text-gray-900 dark:text-white">{{ $section->title }}</h2>
                    <ul class="mt-2 space-y-1 text-sm text-gray-600 dark:text-gray-300">
                        @forelse ($section->lessons as $lesson)
                            <li class="flex items-center gap-2">
                                <span>&#9656;</span> {{ $lesson->title }}
                                @if ($lesson->is_preview)
                                    <span class="rounded bg-green-100 dark:bg-green-900 px-1.5 py-0.5 text-xs text-green-700 dark:text-green-300">Preview</span>
                                @endif
                            </li>
                        @empty
                            <li class="text-gray-400">Lessons coming soon.</li>
                        @endforelse
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.site>
