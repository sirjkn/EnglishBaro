<x-layouts.student :title="$track->track_code.' '.$level->title">
    <a href="{{ route('student.tracks.show', $track) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
        &larr; Back to Track {{ $track->track_code }}
    </a>

    <h1 class="mt-3 text-2xl font-bold text-gray-900 dark:text-white">{{ $level->title }}</h1>
    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $track->track_code }} &middot; {{ $track->name }}</p>

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
        @foreach ($level->sections as $section)
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                <h2 class="font-semibold text-gray-900 dark:text-white">{{ $section->title }}</h2>
                <ul class="mt-3 space-y-1">
                    @forelse ($section->lessons as $lesson)
                        <li>
                            <a href="{{ route('student.tracks.learn', [$track, $level, $lesson]) }}"
                               class="flex items-center gap-2 rounded-lg px-2 py-2 text-sm text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700">
                                @if ($completedLessonIds->contains($lesson->id))
                                    <span class="text-green-500">&#10003;</span>
                                @else
                                    <span class="h-2 w-2 rounded-full border border-gray-300 dark:border-gray-600"></span>
                                @endif
                                {{ $lesson->title }}
                            </a>
                        </li>
                    @empty
                        <li class="px-2 py-2 text-sm text-gray-400">No lessons yet.</li>
                    @endforelse
                </ul>
            </div>
        @endforeach
    </div>
</x-layouts.student>
