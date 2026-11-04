@props([
    'label',
    'value',
    'sub' => null,
    'icon' => null,
    'color' => 'indigo',
    'labelSize' => 'text-xs',
    'compact' => false,
])

@php
    $colorClasses = [
        'indigo' => ['icon' => 'bg-indigo-500 text-white', 'blob' => 'bg-indigo-500/10'],
        'emerald' => ['icon' => 'bg-emerald-500 text-white', 'blob' => 'bg-emerald-500/10'],
        'purple' => ['icon' => 'bg-purple-500 text-white', 'blob' => 'bg-purple-500/10'],
        'amber' => ['icon' => 'bg-amber-500 text-white', 'blob' => 'bg-amber-500/10'],
        'teal' => ['icon' => 'bg-teal-500 text-white', 'blob' => 'bg-teal-500/10'],
        'rose' => ['icon' => 'bg-rose-500 text-white', 'blob' => 'bg-rose-500/10'],
        'cyan' => ['icon' => 'bg-cyan-500 text-white', 'blob' => 'bg-cyan-500/10'],
    ];

    $palette = $colorClasses[$color] ?? $colorClasses['indigo'];
@endphp

<div {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-2xl border border-black/10 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-800']) }}>
    <div class="pointer-events-none absolute -bottom-6 -right-6 h-20 w-20 rounded-full {{ $palette['blob'] }}"></div>

    <div class="relative">
        @if ($icon)
            <div class="flex items-center justify-center rounded-xl {{ $palette['icon'] }} {{ $compact ? 'h-7 w-7' : 'h-9 w-9' }}">
                <x-dynamic-component :component="'icons.' . $icon" class="{{ $compact ? 'h-3.5 w-3.5' : 'h-4 w-4' }}" />
            </div>
        @endif

        <p class="mt-3 font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400 {{ $labelSize }}">{{ $label }}</p>
        <p class="mt-0.5 font-bold text-gray-900 dark:text-white {{ $compact ? 'text-sm' : 'text-xl' }}">{{ $value }}</p>
        @if ($sub)
            <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">{{ $sub }}</p>
        @endif
    </div>
</div>
