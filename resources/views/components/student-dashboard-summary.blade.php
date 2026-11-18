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

<div class="relative overflow-hidden rounded-xl border border-blue-100 bg-gradient-to-br from-blue-50 to-indigo-100 p-4 sm:p-6 dark:border-gray-700 dark:from-gray-800 dark:to-gray-800">
    <div class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-blue-200/50 dark:bg-blue-900/20"></div>

    <div class="relative grid grid-cols-1 gap-4 sm:gap-6 lg:grid-cols-2 lg:divide-x lg:divide-blue-200/60 dark:lg:divide-gray-700">
        <div class="flex flex-col justify-between">
            {{-- Square photo beside the identity, so the card stays short on phones --}}
            <div class="flex items-center gap-3.5 sm:gap-4">
                <x-student-avatar :profile="$studentProfile" size="h-[76px] w-[76px] sm:h-20 sm:w-20" class="!rounded-lg border-2 border-white shadow-sm dark:border-gray-700" />
                <div class="min-w-0">
                    <h1 class="truncate text-xl font-extrabold text-gray-900 sm:text-2xl dark:text-white">{{ $user->name }}</h1>
                    <p class="mt-0.5 text-sm font-semibold text-blue-700 dark:text-blue-300">{{ $studentProfile?->student_id ?? '—' }}</p>
                    <p class="mt-0.5 flex min-w-0 items-center gap-1.5 text-xs text-gray-500 sm:text-sm dark:text-gray-400">
                        <x-icons.envelope class="h-4 w-4 flex-none" /> <span class="truncate">{{ $user->email }}</span>
                    </p>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-2 sm:flex sm:flex-wrap">
                <x-student-profile-modal :student-profile="$studentProfile" />
                <x-student-password-modal />
            </div>

            <div class="mt-3 grid grid-cols-2 gap-2 sm:flex sm:flex-wrap">
                @if ($activeSubscription)
                    <span class="inline-flex items-center justify-center gap-1.5 rounded-md border border-green-300 bg-green-50 px-2.5 py-1.5 text-xs font-semibold text-green-700 dark:border-green-800 dark:bg-green-900/30 dark:text-green-300">
                        <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span> Active
                    </span>
                @endif
                <span class="inline-flex items-center justify-center whitespace-nowrap rounded-md border border-gray-300 bg-white px-2 py-1.5 text-[11px] font-medium text-gray-700 sm:px-2.5 sm:text-xs dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">Member since {{ $user->created_at->format('M Y') }}</span>
                @if ($studentProfile?->track)
                    <span class="inline-flex items-center justify-center rounded-md border border-indigo-300 bg-indigo-50 px-2.5 py-1.5 text-xs font-semibold text-indigo-700 dark:border-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">Current Track: {{ $studentProfile->track->track_code }}</span>
                @endif
                <span class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ $enrollments->count() }} Enrollment{{ $enrollments->count() === 1 ? '' : 's' }}</span>
            </div>
        </div>

        <div class="flex flex-col items-stretch gap-2 border-t border-blue-200/70 pt-4 lg:items-end lg:border-t-0 lg:pl-6 lg:pt-0 dark:border-gray-700">
            @if ($activeSubscription)
                <x-subscription-countdown :subscription="$activeSubscription" class="justify-center lg:justify-start" />
            @endif
            <div class="grid w-full grid-cols-3 gap-1.5 sm:gap-2">
                <x-stat-card
                    label="Track Expires"
                    :value="$nearestExpiry ? $nearestExpiry->expires_at->format('M j') : 'N/A'"
                    icon="clock"
                    color="indigo"
                    compact
                    class="w-full !p-2"
                    label-size="text-[9px]"
                />
                <x-stat-card
                    label="Sessions"
                    :value="$sessionsThisMonth.'/'.$sessionMonthlyLimit"
                    icon="bolt"
                    color="amber"
                    compact
                    class="w-full !p-2"
                    label-size="text-[9px]"
                />
                <x-stat-card
                    label="Total Paid"
                    :value="$paidCurrency.' '.number_format((float) $totalPaid, 2)"
                    icon="credit-card"
                    color="emerald"
                    compact
                    class="w-full !p-2"
                    label-size="text-[9px]"
                />
            </div>
        </div>
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
