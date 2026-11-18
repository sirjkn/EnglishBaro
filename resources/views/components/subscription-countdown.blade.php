@props(['subscription'])

@if ($subscription)
    @php
        $daysLeft = max(0, now()->startOfDay()->diffInDays($subscription->expires_at->copy()->startOfDay(), false));
        $isUrgent = $daysLeft <= 3;
        $colorClasses = 'border border-white bg-yellow-400 text-white [text-shadow:0_1px_2px_rgba(0,0,0,0.9),0_-1px_1px_rgba(0,0,0,0.6),1px_0_1px_rgba(0,0,0,0.6),-1px_0_1px_rgba(0,0,0,0.6)] dark:border-gray-600 dark:bg-yellow-500'.($isUrgent ? ' animate-pulse' : '');
    @endphp
    <div {{ $attributes->merge(['class' => 'flex max-w-full items-center gap-2 whitespace-nowrap rounded-md px-4 py-3 text-base font-semibold tracking-tight sm:gap-2 sm:px-4 sm:py-3 sm:text-sm '.$colorClasses]) }}>
        <x-icons.medal class="h-5 w-5 shrink-0 sm:h-5 sm:w-5" />
        @if ($daysLeft > 0)
            <span class="truncate">{{ $daysLeft }} day{{ $daysLeft === 1 ? '' : 's' }} left<span class="hidden sm:inline"> on subscription</span></span>
        @else
            <span class="truncate">Subscription expired</span>
        @endif
    </div>
@endif
