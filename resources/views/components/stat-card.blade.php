@props(['label', 'value'])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800']) }}>
    <p class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ $label }}</p>
    <p class="mt-1 text-xl font-bold text-gray-900 dark:text-white">{{ $value }}</p>
</div>
