<x-layouts.student :title="'My Courses'">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Courses</h1>

    @if ($enrollments->isEmpty())
        <x-empty-state class="mt-6" message="You have not enrolled in any courses yet.">
        </x-empty-state>
        <div class="mt-4 text-center">
            <a href="{{ route('courses.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">Explore Courses &rarr;</a>
        </div>
    @else
        <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($enrollments as $enrollment)
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                    <div class="aspect-video bg-gradient-to-br from-indigo-100 to-indigo-300 dark:from-indigo-900 dark:to-indigo-700"></div>
                    <div class="p-5">
                        <span class="text-xs font-medium uppercase text-indigo-600 dark:text-indigo-400">{{ $enrollment->course->level?->name ?? 'All Levels' }}</span>
                        <h3 class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $enrollment->course->title }}</h3>

                        <div class="mt-3 h-2 w-full rounded-full bg-gray-100 dark:bg-gray-700">
                            <div class="h-2 rounded-full bg-indigo-600" style="width: {{ $enrollment->progress->percent_complete ?? 0 }}%"></div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ $enrollment->progress->lessons_completed ?? 0 }} / {{ $enrollment->progress->total_lessons ?? 0 }} lessons
                            @if ($enrollment->subscription)
                                &middot; Expires {{ $enrollment->subscription->expires_at->format('M d, Y') }}
                            @endif
                        </p>

                        <a href="{{ route('student.courses.show-learn', $enrollment->course) }}" class="mt-4 block w-full rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white hover:bg-indigo-500">
                            Continue Learning
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.student>
