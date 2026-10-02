@props([
    'label',
    'value',
    'icon' => null,
    'color' => 'indigo',
])

@php
    $colorClasses = [
        'indigo' => 'bg-indigo-500',
        'emerald' => 'bg-emerald-500',
        'purple' => 'bg-purple-500',
        'amber' => 'bg-amber-500',
        'teal' => 'bg-teal-500',
        'rose' => 'bg-rose-500',
        'cyan' => 'bg-cyan-500',
    ];

    $bg = $colorClasses[$color] ?? $colorClasses['indigo'];
@endphp

@if ($icon)
    <div {{ $attributes->merge(['class' => "rounded-2xl {$bg} p-5 shadow-sm"]) }}>
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl border-2 border-white/70 text-white">
            <x-dynamic-component :component="'icons.' . $icon" class="h-5 w-5" />
        </div>

        <p class="mt-4 text-xs font-bold uppercase tracking-wide text-white/90">{{ $label }}</p>
        <p class="mt-1 text-2xl font-bold text-white">{{ $value }}</p>
    </div>
@else
    <div {{ $attributes->merge(['class' => 'rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800']) }}>
        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $label }}</p>
        <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $value }}</p>
    </div>
@endif
