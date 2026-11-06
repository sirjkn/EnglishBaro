<x-layouts.student :title="'Dashboard'">
    @php
        $user = auth()->user();
    @endphp

    <x-student-dashboard-summary
        :student-profile="$studentProfile"
        :active-subscription="$activeSubscription"
        :enrollments="$enrollments"
        :needs-placement-test="$needsPlacementTest"
        :upcoming-track="$upcomingTrack"
        :nearest-expiry="$nearestExpiry"
        :sessions-this-month="$sessionsThisMonth"
        :session-monthly-limit="$sessionMonthlyLimit"
        :latest-session="$latestSession"
        :paid-currency="$paidCurrency"
        :total-paid="$totalPaid"
        :successful-payments-count="$successfulPaymentsCount"
    />

    <div class="mt-8">
        <div class="flex items-center justify-between">
            <h2 class="flex items-center gap-2 text-lg font-bold text-gray-900 dark:text-white">
                <span class="flex h-7 w-7 items-center justify-center rounded-md bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300">
                    <x-icons.book-open class="h-4 w-4" />
                </span>
                My Progress
            </h2>
            <a href="{{ route('home') }}#explore-course" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">View all &rarr;</a>
        </div>

        @php
            $startedEnrollments = $enrollments->filter(fn ($enrollment) => $enrollment->last_viewed_lesson_id !== null
                || ($enrollment->progress->percent_complete ?? 0) >= 100
            );
        @endphp

        @if ($startedEnrollments->isEmpty())
            <x-empty-state class="mt-4" message="You haven't started a course yet." />
        @else
            <div class="mt-4 space-y-3">
                @foreach ($startedEnrollments as $enrollment)
                    @php
                        $isCompleted = ($enrollment->progress->percent_complete ?? 0) >= 100;
                    @endphp
                    <div class="group relative flex items-center gap-4 overflow-hidden rounded-2xl border border-gray-100 bg-white p-3 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg dark:border-white/10 dark:bg-gray-800 sm:gap-5 sm:p-4">
                        <div class="aspect-square w-20 shrink-0 overflow-hidden rounded-xl bg-gradient-to-br from-indigo-100 to-indigo-300 dark:from-indigo-900 dark:to-indigo-700 sm:w-28">
                            @if ($enrollment->track->thumbnail?->resolved_url)
                                <img src="{{ $enrollment->track->thumbnail->resolved_url }}" alt="{{ $enrollment->track->name }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center">
                                    <span class="text-xl font-black text-indigo-700 dark:text-indigo-200 sm:text-2xl">{{ $enrollment->track->track_code }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <span class="text-xs font-medium uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Track {{ $enrollment->track->track_code }}</span>
                            <h3 class="truncate text-base font-bold text-gray-900 dark:text-white">{{ $enrollment->track->name }}</h3>

                            <div class="mt-2 h-2 w-full max-w-xs rounded-full bg-gray-100 dark:bg-gray-700">
                                <div class="h-2 rounded-full bg-indigo-600" style="width: {{ $enrollment->progress->percent_complete ?? 0 }}%"></div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ $enrollment->progress->levels_completed ?? 0 }} / {{ $enrollment->progress->total_levels ?? 0 }} levels
                                &middot; {{ $enrollment->progress->lessons_completed ?? 0 }} / {{ $enrollment->progress->total_lessons ?? 0 }} lessons
                            </p>
                        </div>

                        @if ($isCompleted)
                            <span class="shrink-0 rounded-lg bg-green-100 px-3 py-2 text-center text-xs font-semibold text-green-700 dark:bg-green-900/40 dark:text-green-300 sm:px-4 sm:text-sm">
                                Completed
                            </span>
                        @else
                            <a href="{{ route('student.tracks.show', $enrollment->track) }}" class="shrink-0 rounded-lg bg-indigo-600 px-3 py-2 text-center text-xs font-semibold text-white transition group-hover:bg-indigo-500 sm:px-4 sm:text-sm">
                                Continue Learning
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.student>
