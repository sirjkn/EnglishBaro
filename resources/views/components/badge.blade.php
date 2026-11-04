@props(['color' => 'gray', 'dot' => false])

@php
    $colors = [
        'gray' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
        'green' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
        'red' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
        'yellow' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300',
        'indigo' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
        'black' => 'bg-gray-800 text-white dark:bg-black dark:text-gray-200',
    ];

    $dotColors = [
        'gray' => 'bg-gray-500',
        'green' => 'bg-green-500',
        'red' => 'bg-red-500',
        'yellow' => 'bg-yellow-500',
        'indigo' => 'bg-indigo-500',
        'black' => 'bg-gray-300',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium '.($colors[$color] ?? $colors['gray'])]) }}>
    @if ($dot)
        <span class="h-1.5 w-1.5 rounded-full {{ $dotColors[$color] ?? $dotColors['gray'] }}"></span>
    @endif
    {{ $slot }}
</span>
