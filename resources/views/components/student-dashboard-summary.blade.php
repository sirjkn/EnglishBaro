@props([
    'studentProfile',
    'activeSubscription',
    'enrollments',
    'needsPlacementTest',
    'upcomingTrack',
    'nearestExpiry',
    'sessionsThisMonth',
    'sessionMonthlyLimit',
    'latestSession',
    'paidCurrency',
    'totalPaid',
    'successfulPaymentsCount',
])

@php
    $user = auth()->user();
@endphp

<div class="relative overflow-hidden rounded-xl border border-blue-100 bg-gradient-to-br from-blue-50 to-indigo-100 p-6 dark:border-gray-700 dark:from-gray-800 dark:to-gray-800">
    <div class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-blue-200/50 dark:bg-blue-900/20"></div>

    <div class="relative flex flex-col items-start gap-4 lg:flex-row lg:items-start lg:justify-between">
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

        <div class="flex w-full flex-col items-end gap-2 lg:w-auto">
            @if ($activeSubscription)
                <x-subscription-countdown :subscription="$activeSubscription" />
            @endif

            <div class="grid w-full grid-cols-3 gap-1.5 sm:w-auto sm:gap-2">
                <x-stat-card
                    label="Track Expires"
                    :value="$nearestExpiry ? $nearestExpiry->expires_at->format('M j') : 'N/A'"
                    icon="clock"
                    color="indigo"
                    compact
                    class="w-full sm:w-28 !p-2"
                    label-size="text-[9px]"
                />
                <x-stat-card
                    label="Sessions"
                    :value="$sessionsThisMonth.'/'.$sessionMonthlyLimit"
                    icon="bolt"
                    color="amber"
                    compact
                    class="w-full sm:w-28 !p-2"
                    label-size="text-[9px]"
                />
                <x-stat-card
                    label="Total Paid"
                    :value="$paidCurrency.' '.number_format((float) $totalPaid, 2)"
                    icon="credit-card"
                    color="emerald"
                    compact
                    class="w-full sm:w-28 !p-2"
                    label-size="text-[9px]"
                />
            </div>
        </div>
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
        <x-payment-method-modal :course="$upcomingTrack->course" label="Pay for {{ $upcomingTrack->course->name }}" class="shrink-0 rounded-md bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600" />
    </div>
@endif
