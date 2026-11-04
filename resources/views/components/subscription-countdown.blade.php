@props(['subscription'])

@if ($subscription)
    @php
        $daysLeft = max(0, now()->startOfDay()->diffInDays($subscription->expires_at->copy()->startOfDay(), false));
        $isUrgent = $daysLeft <= 3;
        $colorClasses = 'bg-orange-500 shadow-lg shadow-orange-500/40'.($isUrgent ? ' animate-pulse' : '');
    @endphp
    <div {{ $attributes->merge(['class' => 'flex max-w-full items-center gap-1.5 whitespace-nowrap rounded-full px-3 py-1.5 text-sm font-normal tracking-tight sm:gap-1.5 sm:px-3 sm:py-1.5 sm:text-xs '.$colorClasses]) }}>
        <x-icons.medal class="h-4 w-4 shrink-0 sm:h-4 sm:w-4 text-white" />
        @if ($daysLeft > 0)
            <span class="truncate text-white">{{ $daysLeft }} day{{ $daysLeft === 1 ? '' : 's' }} left<span class="hidden sm:inline"> on subscription</span></span>
        @else
            <span class="truncate text-white">Subscription expired</span>
        @endif
    </div>
@endif
