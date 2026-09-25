@props([
    'label',
    'value',
    'icon' => null,
    'iconBg' => 'bg-indigo-500 text-white',
    'blob' => 'bg-indigo-500/10',
])

<div {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800']) }}>
    <div class="pointer-events-none absolute -bottom-6 -right-6 h-24 w-24 rounded-full {{ $blob }}"></div>

    <div class="relative">
        @if ($icon)
            <div class="flex h-11 w-11 items-center justify-center rounded-xl {{ $iconBg }}">
                <x-dynamic-component :component="'icons.' . $icon" class="h-5 w-5" />
            </div>
        @endif

        <p class="mt-4 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $label }}</p>
        <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $value }}</p>
    </div>
</div>
