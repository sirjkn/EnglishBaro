@props(['subscription'])

@if ($subscription)
    @php
        $daysLeft = max(0, now()->startOfDay()->diffInDays($subscription->expires_at->copy()->startOfDay(), false));
        $isUrgent = $daysLeft <= 3;
        $colorClasses = $isUrgent
            ? 'bg-red-500 text-white shadow-lg shadow-red-500/40 animate-pulse'
            : 'bg-amber-400 text-indigo-950 shadow-lg shadow-amber-400/40';
    @endphp
    <div {{ $attributes->merge(['class' => 'flex items-center gap-2 rounded-full px-5 py-2.5 text-base font-extrabold tracking-tight '.$colorClasses]) }}>
        <x-icons.medal class="h-5 w-5" />
        @if ($daysLeft > 0)
            {{ $daysLeft }} day{{ $daysLeft === 1 ? '' : 's' }} left on subscription
        @else
            Subscription expired
        @endif
    </div>
@endif
