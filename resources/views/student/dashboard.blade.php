<x-layouts.student :title="'Dashboard'">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Welcome back, {{ auth()->user()->name }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Student ID: {{ $studentProfile?->student_id ?? '—' }} &middot; Level: {{ $studentProfile?->level?->name ?? 'Not set' }}
            </p>
        </div>
        <x-subscription-countdown :subscription="$upcomingPayment" />
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Active Courses</p>
            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $enrollments->count() }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Upcoming Payment</p>
            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                @if ($upcomingPayment)
                    {{ $upcomingPayment->expires_at->format('M d, Y') }}
                @else
                    None
                @endif
            </p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Notifications</p>
            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $recentNotifications->whereNull('read_at')->count() }} unread</p>
        </div>
    </div>

    <div class="mt-8">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">My Courses</h2>
            <a href="{{ route('student.courses.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">View all &rarr;</a>
        </div>

        @if ($enrollments->isEmpty())
            <x-empty-state class="mt-4" message="You are not enrolled in any courses yet." />
        @else
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($enrollments as $enrollment)
                    <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-start justify-between gap-2">
                            <span class="text-xs font-medium uppercase text-indigo-600 dark:text-indigo-400">{{ $enrollment->course->level?->name ?? 'All Levels' }}</span>
                            <x-subscription-countdown :subscription="$enrollment->subscription" class="shrink-0" />
                        </div>
                        <h3 class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $enrollment->course->title }}</h3>

                        <div class="mt-3 h-2 w-full rounded-full bg-gray-100 dark:bg-gray-700">
                            <div class="h-2 rounded-full bg-indigo-600" style="width: {{ $enrollment->progress->percent_complete ?? 0 }}%"></div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ $enrollment->progress->lessons_completed ?? 0 }} / {{ $enrollment->progress->total_lessons ?? 0 }} lessons complete
                        </p>

                        <a href="{{ route('student.courses.show-learn', $enrollment->course) }}" class="mt-4 block w-full rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white hover:bg-indigo-500">
                            Continue Learning
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="mt-8">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Notifications</h2>

        @if ($recentNotifications->isEmpty())
            <x-empty-state class="mt-4" message="No notifications yet." />
        @else
            <div class="mt-4 divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white dark:divide-gray-700 dark:border-gray-700 dark:bg-gray-800">
                @foreach ($recentNotifications as $notification)
                    <div class="flex items-center justify-between p-4">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $notification->title }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $notification->created_at->diffForHumans() }}</p>
                        </div>
                        @unless ($notification->read_at)
                            <span class="h-2 w-2 rounded-full bg-indigo-600"></span>
                        @endunless
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.student>
