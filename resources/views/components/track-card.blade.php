@props(['track', 'pricingRegion' => null])

@php
    $displayPrice = $track->priceForRegion($pricingRegion);
@endphp

<a href="{{ route('tracks.show', $track) }}" class="group flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800">
    <div class="aspect-[4/3] w-full overflow-hidden bg-gradient-to-br from-indigo-100 to-indigo-300 dark:from-indigo-900 dark:to-indigo-700">
        @if ($track->thumbnail?->resolved_url)
            <img src="{{ $track->thumbnail->resolved_url }}" alt="{{ $track->name }}" class="h-full w-full object-cover">
        @else
            <div class="flex h-full w-full items-center justify-center">
                <span class="text-5xl font-black text-indigo-700 dark:text-indigo-200">{{ $track->track_code }}</span>
            </div>
        @endif
    </div>

    <div class="flex flex-1 flex-col p-5">
        <h3 class="text-base font-bold text-gray-900 dark:text-white">
            {{ $track->name }}
        </h3>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            Track {{ $track->track_code }} &middot; {{ $track->levels_count ?? $track->levels()->count() }} levels &middot; {{ $track->subscription_days }} days access
        </p>

        <div class="mt-auto flex items-center justify-between pt-5">
            <span class="text-sm font-bold text-gray-900 dark:text-white">
                {{ $track->currency }} {{ number_format($displayPrice, 2) }}
                @if ($pricingRegion)
                    <span class="ml-1 text-[10px] font-normal uppercase text-gray-400" title="Price for {{ $pricingRegion }}">{{ $pricingRegion }}</span>
                @endif
            </span>
        </div>

        <span class="mt-4 block w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-center text-sm font-semibold text-white transition group-hover:bg-indigo-500">
            View Track
        </span>
    </div>
</a>
