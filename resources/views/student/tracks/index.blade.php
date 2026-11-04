<x-layouts.student :title="'My Courses'">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Courses</h1>
        <x-subscription-countdown :subscription="$subscription" />
    </div>

    @if ($enrollments->isEmpty())
        <x-empty-state class="mt-6" message="You have not enrolled in any tracks yet."></x-empty-state>
        <div class="mt-4 text-center">
            <a href="{{ route('tracks.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">Explore Course Tracks &rarr;</a>
        </div>
    @else
        <div class="mt-6 space-y-3">
            @foreach ($enrollments as $enrollment)
                @php
                    $trackOrder = $enrollment->track->order;
                    $isLocked = $currentTrackOrder !== null && $trackOrder > $currentTrackOrder;
                    $isCompleted = ($enrollment->progress->percent_complete ?? 0) >= 100
                        || ($currentTrackOrder !== null && $trackOrder < $currentTrackOrder);
                @endphp
                <div class="group relative flex items-center gap-4 overflow-hidden rounded-2xl border border-gray-100 bg-white p-3 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800 sm:gap-5 sm:p-4 {{ $isLocked ? 'opacity-60' : '' }}">
                    <div class="relative aspect-square w-20 shrink-0 overflow-hidden rounded-xl bg-gradient-to-br from-indigo-100 to-indigo-300 dark:from-indigo-900 dark:to-indigo-700 sm:w-28">
                        @if ($enrollment->track->thumbnail?->resolved_url)
                            <img src="{{ $enrollment->track->thumbnail->resolved_url }}" alt="{{ $enrollment->track->name }}" class="h-full w-full object-cover {{ $isLocked ? 'grayscale' : '' }}">
                        @else
                            <div class="flex h-full w-full items-center justify-center">
                                <span class="text-xl font-black text-indigo-700 dark:text-indigo-200 sm:text-2xl">{{ $enrollment->track->track_code }}</span>
                            </div>
                        @endif

                        @if ($isLocked)
                            <div class="absolute inset-0 flex items-center justify-center bg-black/30">
                                <x-icons.lock class="h-5 w-5 text-white sm:h-6 sm:w-6" />
                            </div>
                        @endif
                    </div>

                    <div class="min-w-0 flex-1">
                        <span class="text-xs font-medium uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Track {{ $enrollment->track->track_code }}</span>
                        <h3 class="truncate text-base font-bold text-gray-900 dark:text-white">{{ $enrollment->track->name }}</h3>

                        <div class="mt-2 h-2 w-full max-w-xs rounded-full bg-gray-100 dark:bg-gray-700">
                            <div class="h-2 rounded-full {{ $isCompleted ? 'bg-green-500' : 'bg-indigo-600' }}" style="width: {{ $isCompleted ? 100 : ($enrollment->progress->percent_complete ?? 0) }}%"></div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ $enrollment->progress->levels_completed ?? 0 }} / {{ $enrollment->progress->total_levels ?? 0 }} levels
                            &middot; {{ $enrollment->progress->lessons_completed ?? 0 }} / {{ $enrollment->progress->total_lessons ?? 0 }} lessons
                        </p>
                    </div>

                    <div class="shrink-0">
                        @if ($isCompleted)
                            <span class="flex items-center justify-center gap-1.5 rounded-lg bg-green-100 px-3 py-2 text-center text-xs font-semibold text-green-700 dark:bg-green-900/30 dark:text-green-300 sm:px-4 sm:text-sm">
                                <x-icons.clipboard-check class="h-4 w-4" />
                                <span class="hidden sm:inline">Completed</span>
                            </span>
                        @elseif ($isLocked)
                            <span class="flex cursor-not-allowed items-center justify-center gap-1.5 rounded-lg bg-gray-200 px-3 py-2 text-center text-xs font-semibold text-gray-500 dark:bg-gray-700 dark:text-gray-400 sm:px-4 sm:text-sm">
                                <x-icons.lock class="h-4 w-4" />
                                <span class="hidden sm:inline">Locked</span>
                            </span>
                        @else
                            <a href="{{ route('student.tracks.show', $enrollment->track) }}" class="block rounded-lg bg-indigo-600 px-3 py-2 text-center text-xs font-semibold text-white transition group-hover:bg-indigo-500 sm:px-4 sm:text-sm">
                                {{ $enrollment->last_viewed_lesson_id ? 'Continue Learning' : 'Start Learning' }}
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.student>
