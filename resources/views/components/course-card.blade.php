@props(['course', 'pricingRegion' => null])

@php
    $displayPrice = $course->priceForRegion($pricingRegion);
@endphp

<a href="{{ route('courses.show', $course) }}" class="group flex flex-col overflow-hidden rounded-xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm transition hover:shadow-md">
    <div class="aspect-video w-full overflow-hidden bg-gradient-to-br from-indigo-100 to-indigo-300 dark:from-indigo-900 dark:to-indigo-700 flex items-center justify-center">
        @if ($course->thumbnail?->resolved_url)
            <img src="{{ $course->thumbnail->resolved_url }}" alt="{{ $course->title }}" class="h-full w-full object-cover">
        @else
            <span class="text-4xl">🎓</span>
        @endif
    </div>
    <div class="flex flex-1 flex-col p-5">
        <span class="text-xs font-medium uppercase tracking-wide text-indigo-600 dark:text-indigo-400">
            {{ $course->level?->name ?? 'All Levels' }}
        </span>
        <h3 class="mt-1 text-base font-semibold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400">
            {{ $course->title }}
        </h3>
        <div class="mt-auto flex items-center justify-between pt-4">
            <span class="text-sm font-bold text-gray-900 dark:text-white">
                {{ $course->currency }} {{ number_format($displayPrice, 2) }}
                @if ($pricingRegion)
                    <span class="ml-1 text-[10px] font-normal uppercase text-gray-400" title="Price for {{ $pricingRegion }}">{{ $pricingRegion }}</span>
                @endif
            </span>
            <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400">More Details &rarr;</span>
        </div>
    </div>
</a>
