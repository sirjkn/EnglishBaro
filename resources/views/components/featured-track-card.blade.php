@props(['track', 'pricingRegion' => null])

@php
    $displayPrice = $track->priceForRegion($pricingRegion);

    $authUser = auth()->user();
    $enrollment = $authUser
        ? $track->enrollments()->where('user_id', $authUser->id)->where('status', 'active')->with('progress')->first()
        : null;

    $badge = match (true) {
        (bool) $enrollment?->progress?->isComplete() => 'completed',
        (bool) $enrollment => 'enrolled',
        $authUser && ! $authUser->can('enroll', $track) => 'locked',
        default => null,
    };
@endphp

<a href="{{ route('tracks.show', $track) }}" class="group flex flex-col overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800 sm:rounded-2xl">
    <div class="relative aspect-[4/3] w-full max-h-28 overflow-hidden bg-gradient-to-br from-indigo-100 to-indigo-300 dark:from-indigo-900 dark:to-indigo-700 sm:max-h-none">
        @if ($track->thumbnail?->resolved_url)
            <img src="{{ $track->thumbnail->resolved_url }}" alt="{{ $track->name }}" class="h-full w-full object-cover">
        @else
            <div class="flex h-full w-full items-center justify-center">
                <span class="text-2xl font-black text-indigo-700 dark:text-indigo-200 sm:text-5xl">{{ $track->track_code }}</span>
            </div>
        @endif

        @if ($badge === 'enrolled')
            <span class="absolute right-2 top-2 rounded-full bg-amber-400 px-2 py-0.5 text-[10px] font-bold text-gray-900 shadow-sm sm:right-3 sm:top-3 sm:px-3 sm:py-1 sm:text-xs">Enrolled</span>
        @elseif ($badge === 'completed')
            <span class="absolute right-2 top-2 rounded-full bg-green-500 px-2 py-0.5 text-[10px] font-bold text-white shadow-sm sm:right-3 sm:top-3 sm:px-3 sm:py-1 sm:text-xs">Completed</span>
        @elseif ($badge === 'locked')
            <span class="absolute right-2 top-2 flex items-center gap-1 rounded-full bg-indigo-600 px-2 py-0.5 text-[10px] font-bold text-white shadow-sm sm:right-3 sm:top-3 sm:px-3 sm:py-1 sm:text-xs">
                <x-icons.lock class="h-3 w-3" />
                Locked
            </span>
        @endif
    </div>

    <div class="flex flex-1 flex-col p-3 sm:p-5">
        <h3 class="text-sm font-bold text-gray-900 dark:text-white sm:text-base">
            {{ $track->name }}
        </h3>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 sm:mt-2 sm:text-sm">
            Track {{ $track->track_code }} &middot; {{ $track->levels_count ?? $track->levels()->count() }} levels &middot; {{ $track->course->subscription_days }} days access
        </p>

        <div class="mt-auto flex items-center justify-between pt-3 sm:pt-5">
            <span class="text-xs font-bold text-gray-900 dark:text-white sm:text-sm">
                {{ $track->course->currency }} {{ number_format($displayPrice, 2) }}
                @if ($pricingRegion)
                    <span class="ml-1 text-[10px] font-normal uppercase text-gray-400" title="Price for {{ $pricingRegion }}">{{ $pricingRegion }}</span>
                @endif
            </span>
        </div>

        <span class="mt-3 block w-full rounded-lg bg-indigo-600 px-3 py-2 text-center text-xs font-semibold text-white transition group-hover:bg-indigo-500 sm:mt-4 sm:px-4 sm:py-2.5 sm:text-sm">
            View Track
        </span>
    </div>
</a>
