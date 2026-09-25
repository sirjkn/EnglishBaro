@props(['label', 'value', 'color' => 'blue', 'icon' => null, 'sub' => null])

@php
    $palettes = [
        'blue' => [
            'bg' => 'bg-blue-50 dark:bg-blue-950/40',
            'border' => 'border-blue-200 dark:border-blue-900',
            'label' => 'text-blue-600 dark:text-blue-300',
            'value' => 'text-blue-950 dark:text-white',
            'chip' => 'bg-blue-500',
        ],
        'green' => [
            'bg' => 'bg-emerald-50 dark:bg-emerald-950/40',
            'border' => 'border-emerald-200 dark:border-emerald-900',
            'label' => 'text-emerald-600 dark:text-emerald-300',
            'value' => 'text-emerald-950 dark:text-white',
            'chip' => 'bg-emerald-500',
        ],
        'red' => [
            'bg' => 'bg-red-50 dark:bg-red-950/40',
            'border' => 'border-red-200 dark:border-red-900',
            'label' => 'text-red-600 dark:text-red-300',
            'value' => 'text-red-950 dark:text-white',
            'chip' => 'bg-red-500',
        ],
        'orange' => [
            'bg' => 'bg-orange-50 dark:bg-orange-950/40',
            'border' => 'border-orange-200 dark:border-orange-900',
            'label' => 'text-orange-600 dark:text-orange-300',
            'value' => 'text-orange-950 dark:text-white',
            'chip' => 'bg-orange-500',
        ],
        'yellow' => [
            'bg' => 'bg-yellow-50 dark:bg-yellow-950/40',
            'border' => 'border-yellow-200 dark:border-yellow-900',
            'label' => 'text-yellow-700 dark:text-yellow-300',
            'value' => 'text-yellow-950 dark:text-white',
            'chip' => 'bg-yellow-400',
        ],
    ];

    $palette = $palettes[$color] ?? $palettes['blue'];
@endphp

<div {{ $attributes->merge(['class' => "relative overflow-hidden rounded-xl border {$palette['border']} {$palette['bg']} p-4 shadow-sm"]) }}>
    <div class="absolute -right-4 -top-4 h-16 w-16 rounded-full {{ $palette['chip'] }} opacity-10"></div>
    <p class="text-xs font-semibold uppercase tracking-wide {{ $palette['label'] }}">{{ $label }}</p>
    <p class="mt-1 text-2xl font-bold {{ $palette['value'] }}">{{ $value }}</p>
    @if ($sub)
        <p class="mt-0.5 text-xs font-medium {{ $palette['label'] }}">{{ $sub }}</p>
    @endif
</div>
