@props(['track', 'pricingRegion' => null])

@php
    $displayPrice = $track->priceForRegion($pricingRegion);
@endphp

<a href="{{ route('tracks.show', $track) }}" class="group flex flex-col overflow-hidden rounded-xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-md transition hover:-translate-y-0.5 hover:bg-blue-50 hover:shadow-xl dark:hover:bg-blue-950/20">
    <div class="aspect-video w-full overflow-hidden bg-gradient-to-br from-indigo-100 to-indigo-300 dark:from-indigo-900 dark:to-indigo-700 flex items-center justify-center">
        @if ($track->thumbnail?->resolved_url)
            <img src="{{ $track->thumbnail->resolved_url }}" alt="{{ $track->name }}" class="h-full w-full object-cover">
        @else
            <span class="text-4xl font-black text-indigo-700 dark:text-indigo-200">{{ $track->track_code }}</span>
        @endif
    </div>
    <div class="flex flex-1 flex-col p-5">
        <span class="text-xs font-medium uppercase tracking-wide text-indigo-600 dark:text-indigo-400">
            Track {{ $track->track_code }}
        </span>
        <h3 class="mt-1 text-base font-semibold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400">
            {{ $track->name }}
        </h3>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            {{ $track->levels_count ?? $track->levels()->count() }} levels &middot; {{ $track->subscription_days }} days access
        </p>
        <div class="mt-auto flex items-center justify-between pt-4">
            <span class="text-sm font-bold text-gray-900 dark:text-white">
                {{ $track->currency }} {{ number_format($displayPrice, 2) }}
                @if ($pricingRegion)
                    <span class="ml-1 text-[10px] font-normal uppercase text-gray-400" title="Price for {{ $pricingRegion }}">{{ $pricingRegion }}</span>
                @endif
            </span>
            <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400">More Details &rarr;</span>
        </div>
    </div>
</a>
