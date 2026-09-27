@props(['subscription'])

@if ($subscription)
    @php
        $daysLeft = max(0, now()->startOfDay()->diffInDays($subscription->expires_at->copy()->startOfDay(), false));
        $isUrgent = $daysLeft <= 3;
        $colorClasses = $isUrgent
            ? 'bg-red-500 text-white shadow-lg shadow-red-500/40 animate-pulse'
            : 'bg-amber-400 text-indigo-950 shadow-lg shadow-amber-400/40';
    @endphp
    <div {{ $attributes->merge(['class' => 'flex max-w-full items-center gap-1 whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-extrabold tracking-tight sm:gap-2 sm:px-5 sm:py-2.5 sm:text-base '.$colorClasses]) }}>
        <x-icons.medal class="h-3.5 w-3.5 shrink-0 sm:h-5 sm:w-5" />
        @if ($daysLeft > 0)
            <span class="truncate">{{ $daysLeft }} day{{ $daysLeft === 1 ? '' : 's' }} left<span class="hidden sm:inline"> on subscription</span></span>
        @else
            <span class="truncate">Subscription expired</span>
        @endif
    </div>
@endif
