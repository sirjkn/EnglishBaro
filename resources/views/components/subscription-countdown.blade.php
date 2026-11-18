@props(['subscription'])

@if ($subscription)
    @php
        $daysLeft = max(0, now()->startOfDay()->diffInDays($subscription->expires_at->copy()->startOfDay(), false));
        $isUrgent = $daysLeft <= 3;
        $colorClasses = 'border border-white bg-yellow-400 text-blue-900 dark:border-gray-600 dark:bg-yellow-500'.($isUrgent ? ' animate-pulse' : '');
    @endphp
    <div {{ $attributes->merge(['class' => 'flex max-w-full items-center gap-2 whitespace-nowrap rounded-md px-3.5 py-2.5 text-base font-semibold tracking-tight sm:gap-2 sm:px-3.5 sm:py-2.5 sm:text-sm '.$colorClasses]) }}>
        <x-icons.medal class="h-4 w-4 shrink-0 sm:h-4 sm:w-4" />
        @if ($daysLeft > 0)
            <span class="truncate">{{ $daysLeft }} day{{ $daysLeft === 1 ? '' : 's' }} left<span class="hidden sm:inline"> on subscription</span></span>
        @else
            <span class="truncate">Subscription expired</span>
        @endif
    </div>
@endif
