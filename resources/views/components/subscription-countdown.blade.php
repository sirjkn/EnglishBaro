@props(['subscription'])

@if ($subscription)
    @php
        $daysLeft = max(0, now()->startOfDay()->diffInDays($subscription->expires_at->copy()->startOfDay(), false));
        $isUrgent = $daysLeft <= 3;
        $colorClasses = 'border border-white bg-yellow-400 text-black dark:border-gray-600 dark:bg-yellow-500 dark:text-black'.($isUrgent ? ' animate-pulse' : '');
    @endphp
    <div {{ $attributes->merge(['class' => 'flex max-w-full items-center gap-1.5 whitespace-nowrap rounded-md px-3 py-1.5 text-sm font-semibold tracking-tight sm:gap-1.5 sm:px-3 sm:py-1.5 sm:text-xs '.$colorClasses]) }}>
        <x-icons.medal class="h-4 w-4 shrink-0 sm:h-4 sm:w-4" />
        @if ($daysLeft > 0)
            <span class="truncate">{{ $daysLeft }} day{{ $daysLeft === 1 ? '' : 's' }} left<span class="hidden sm:inline"> on subscription</span></span>
        @else
            <span class="truncate">Subscription expired</span>
        @endif
    </div>
@endif
