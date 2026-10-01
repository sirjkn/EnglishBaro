<x-layouts.student :title="'My Courses'">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Courses</h1>

    @if ($enrollments->isEmpty())
        <x-empty-state class="mt-6" message="You have not enrolled in any tracks yet."></x-empty-state>
        <div class="mt-4 text-center">
            <a href="{{ route('tracks.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">Explore Course Tracks &rarr;</a>
        </div>
    @else
        <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($enrollments as $enrollment)
                <div class="group relative flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800">
                    <div class="aspect-[4/3] w-full overflow-hidden bg-gradient-to-br from-indigo-100 to-indigo-300 dark:from-indigo-900 dark:to-indigo-700">
                        @if ($enrollment->track->thumbnail?->resolved_url)
                            <img src="{{ $enrollment->track->thumbnail->resolved_url }}" alt="{{ $enrollment->track->name }}" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full w-full items-center justify-center">
                                <span class="text-5xl font-black text-indigo-700 dark:text-indigo-200">{{ $enrollment->track->track_code }}</span>
                            </div>
                        @endif
                    </div>
                    <x-subscription-countdown :subscription="$enrollment->subscription" class="absolute right-3 top-3" />

                    <div class="flex flex-1 flex-col p-5">
                        <span class="text-xs font-medium uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Track {{ $enrollment->track->track_code }}</span>
                        <h3 class="mt-1 text-base font-bold text-gray-900 dark:text-white">{{ $enrollment->track->name }}</h3>

                        <div class="mt-3 h-2 w-full rounded-full bg-gray-100 dark:bg-gray-700">
                            <div class="h-2 rounded-full bg-indigo-600" style="width: {{ $enrollment->progress->percent_complete ?? 0 }}%"></div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ $enrollment->progress->levels_completed ?? 0 }} / {{ $enrollment->progress->total_levels ?? 0 }} levels
                            &middot; {{ $enrollment->progress->lessons_completed ?? 0 }} / {{ $enrollment->progress->total_lessons ?? 0 }} lessons
                        </p>

                        <a href="{{ route('student.tracks.show', $enrollment->track) }}" class="mt-4 block w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-center text-sm font-semibold text-white transition group-hover:bg-indigo-500">
                            Continue Learning
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.student>
