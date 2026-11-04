<x-layouts.student :title="'Dashboard'">
    @php
        $user = auth()->user();
        $unreadCount = $recentNotifications->whereNull('read_at')->count();
    @endphp

    <div class="relative overflow-hidden rounded-xl border border-blue-100 bg-gradient-to-br from-blue-50 to-indigo-100 p-6 dark:border-gray-700 dark:from-gray-800 dark:to-gray-800">
        <div class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-blue-200/50 dark:bg-blue-900/20"></div>

        <div class="relative flex flex-col items-start gap-4 pr-20 sm:flex-row sm:items-center sm:justify-between sm:pr-0">
            <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center">
                <x-student-avatar :profile="$studentProfile" size="h-20 w-20" class="border-4 border-white shadow-sm dark:border-gray-700" />
                <div>
                    <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $user->name }}</h1>
                    <p class="mt-0.5 text-sm font-semibold text-blue-700 dark:text-blue-300">{{ $studentProfile?->student_id ?? '—' }}</p>
                    <p class="mt-1 flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
                        <x-icons.envelope class="h-4 w-4" /> {{ $user->email }}
                    </p>
                    @if ($activeSubscription)
                        <div class="mt-1.5">
                            <x-badge color="green" dot>Active</x-badge>
                        </div>
                    @endif
                </div>
            </div>
            @if ($activeSubscription)
                <div class="absolute right-0 top-0">
                    <x-subscription-countdown :subscription="$activeSubscription" />
                </div>
            @endif
        </div>

        <div class="relative mt-5 flex flex-wrap items-center gap-2 border-t border-blue-200/60 pt-4 dark:border-gray-700">
            @if ($studentProfile?->track)
                <x-badge color="indigo">Current Track: {{ $studentProfile->track->track_code }}</x-badge>
            @endif
            <span class="text-xs text-gray-400">&middot;</span>
            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $enrollments->count() }} Enrollment{{ $enrollments->count() === 1 ? '' : 's' }}</span>
            <span class="text-xs text-gray-400">&middot;</span>
            <span class="text-xs text-gray-500 dark:text-gray-400">Member since {{ $user->created_at->format('M Y') }}</span>
            <a href="{{ route('student.account') }}" class="ml-auto flex items-center gap-1 text-xs font-semibold text-blue-700 hover:text-blue-800 dark:text-blue-300 dark:hover:text-blue-200">
                <x-icons.pencil class="h-3 w-3" /> Edit Profile
            </a>
        </div>
    </div>

    @if ($needsPlacementTest)
        <div class="mt-6 flex flex-col items-start justify-between gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 sm:flex-row sm:items-center dark:border-amber-900 dark:bg-amber-950/30">
            <div class="flex items-start gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-400 text-white">
                    <x-icons.medal class="h-5 w-5" />
                </span>
                <div>
                    <p class="text-sm font-semibold text-amber-900 dark:text-amber-200">To do: Take your Placement Test</p>
                    <p class="mt-0.5 text-xs text-amber-700 dark:text-amber-300">We'll use your answers to place you into the right track — that's the track you'll pay for to get started.</p>
                </div>
            </div>
            <a href="{{ route('student.placement-test') }}" class="shrink-0 rounded-md bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600">
                Start Placement Test
            </a>
        </div>
    @elseif ($upcomingTrack)
        <div class="mt-6 flex flex-col items-start justify-between gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 sm:flex-row sm:items-center dark:border-amber-900 dark:bg-amber-950/30">
            <div class="flex items-start gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-400 text-white">
                    <x-icons.credit-card class="h-5 w-5" />
                </span>
                <div>
                    <p class="text-sm font-semibold text-amber-900 dark:text-amber-200">To do: Pay for Track {{ $upcomingTrack->track_code }}</p>
                    <p class="mt-0.5 text-xs text-amber-700 dark:text-amber-300">Your placement put you in {{ $upcomingTrack->track_code }} — {{ $upcomingTrack->name }}. Pay now to unlock it and start learning.</p>
                </div>
            </div>
            <x-payment-method-modal :track="$upcomingTrack" label="Pay for {{ $upcomingTrack->track_code }}" class="shrink-0 rounded-md bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600" />
        </div>
    @endif

    <div class="mt-6 grid grid-cols-3 gap-2 sm:gap-4">
        <x-stat-card
            label="Track Expires"
            :value="$nearestExpiry ? $nearestExpiry->expires_at->format('M j, Y') : 'N/A'"
            :sub="$nearestExpiry ? max(0, (int) floor(now()->diffInDays($nearestExpiry->expires_at, false))).' Days Left' : 'No active subscription'"
            icon="clock"
            color="indigo"
            label-size="text-[10px] sm:text-sm"
        />
        <x-stat-card
            label="Sessions This Month"
            :value="$sessionsThisMonth.' of '.$sessionMonthlyLimit"
            :sub="$latestSession ? $latestSession->device.', '.($latestSession->status === 'active' ? 'active now' : $latestSession->last_activity_at?->diffForHumans()) : 'No sessions yet'"
            icon="bolt"
            color="amber"
            label-size="text-[10px] sm:text-sm"
        />
        <x-stat-card
            label="Total Paid"
            :value="$paidCurrency.' '.number_format((float) $totalPaid, 2)"
            :sub="$successfulPaymentsCount.' successful payment'.($successfulPaymentsCount === 1 ? '' : 's')"
            icon="credit-card"
            color="emerald"
            label-size="text-[10px] sm:text-sm"
        />
    </div>

    <div class="mt-8">
        <div class="flex items-center justify-between">
            <h2 class="flex items-center gap-2 text-lg font-bold text-gray-900 dark:text-white">
                <span class="flex h-7 w-7 items-center justify-center rounded-md bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300">
                    <x-icons.book-open class="h-4 w-4" />
                </span>
                My Courses
            </h2>
            <a href="{{ route('student.tracks.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">View all &rarr;</a>
        </div>

        @if ($enrollments->isEmpty())
            <x-empty-state class="mt-4" message="You are not enrolled in any tracks yet." />
        @else
            <div class="mt-4 space-y-3">
                @foreach ($enrollments as $enrollment)
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

                        <a href="{{ route('student.tracks.show', $enrollment->track) }}" class="shrink-0 rounded-lg bg-indigo-600 px-3 py-2 text-center text-xs font-semibold text-white transition group-hover:bg-indigo-500 sm:px-4 sm:text-sm">
                            {{ $enrollment->last_viewed_lesson_id ? 'Continue Learning' : 'Start Learning' }}
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="mt-8 rounded-xl border border-black/10 bg-white p-6 dark:border-white/10 dark:bg-gray-800">
        <h2 class="flex items-center gap-2 font-bold text-gray-900 dark:text-white">
            <span class="flex h-7 w-7 items-center justify-center rounded-md bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300">
                <x-icons.bell class="h-4 w-4" />
            </span>
            Recent Notifications
        </h2>

        @if ($recentNotifications->isEmpty())
            <x-empty-state class="mt-4" message="No notifications yet." />
        @else
            <div class="mt-4 divide-y divide-gray-100 dark:divide-gray-700">
                @foreach ($recentNotifications as $notification)
                    <a href="{{ $notification->data['url'] ?? route('student.notifications') }}" class="flex items-center justify-between py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $notification->title }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $notification->created_at->diffForHumans() }}</p>
                        </div>
                        @unless ($notification->read_at)
                            <span class="h-2 w-2 flex-none rounded-full bg-indigo-600"></span>
                        @endunless
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.student>
