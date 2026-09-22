@props(['subscription'])

@if ($subscription)
    @php
        $daysLeft = max(0, now()->startOfDay()->diffInDays($subscription->expires_at->copy()->startOfDay(), false));
        $isUrgent = $daysLeft <= 3;
    @endphp
    <div {{ $attributes->merge(['class' => 'flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold '.($isUrgent ? 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300' : 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300')]) }}>
        <x-icons.medal class="h-3.5 w-3.5" />
        @if ($daysLeft > 0)
            {{ $daysLeft }} day{{ $daysLeft === 1 ? '' : 's' }} left on subscription
        @else
            Subscription expired
        @endif
    </div>
@endif
