@props(['message' => 'Nothing to show yet.'])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-dashed border-gray-300 dark:border-gray-700 p-12 text-center']) }}>
    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $message }}</p>
</div>
