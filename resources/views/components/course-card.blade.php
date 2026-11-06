@props(['course', 'pricingRegion' => null])

@php
    $displayPrice = $course->priceForRegion($pricingRegion);

    $authUser = auth()->user();
    $defaultTrack = $course->defaultTrack();

    $badge = match (true) {
        $authUser && $defaultTrack && ! $authUser->can('enroll', $defaultTrack) && $authUser->studentProfile?->track?->course_id === $course->id => 'enrolled',
        $authUser && ! $authUser->can('enroll', $course) => 'locked',
        default => null,
    };
@endphp

<a href="{{ route('courses.show', $course) }}" class="group flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800">
    <div class="relative aspect-[4/3] w-full overflow-hidden bg-gradient-to-br from-indigo-100 to-indigo-300 dark:from-indigo-900 dark:to-indigo-700">
        @if ($course->thumbnail?->resolved_url)
            <img src="{{ $course->thumbnail->resolved_url }}" alt="{{ $course->name }}" class="h-full w-full object-cover">
        @else
            <div class="flex h-full w-full items-center justify-center">
                <span class="text-5xl font-black text-indigo-700 dark:text-indigo-200">{{ $course->course_code }}</span>
            </div>
        @endif

        @if ($badge === 'enrolled')
            <span class="absolute right-3 top-3 rounded-full bg-amber-400 px-3 py-1 text-xs font-bold text-gray-900 shadow-sm">Enrolled</span>
        @elseif ($badge === 'locked')
            <span class="absolute right-3 top-3 flex items-center gap-1 rounded-full bg-indigo-600 px-3 py-1 text-xs font-bold text-white shadow-sm">
                <x-icons.lock class="h-3 w-3" />
                Locked
            </span>
        @endif
    </div>

    <div class="flex flex-1 flex-col p-5">
        <h3 class="text-base font-bold text-gray-900 dark:text-white">
            {{ $course->name }}
        </h3>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            {{ $course->tracks_count ?? $course->tracks()->count() }} units &middot; {{ $course->subscription_days }} days access
        </p>

        <div class="mt-auto pt-4">
            <p class="text-sm font-bold text-gray-900 dark:text-white">
                {{ $course->currency }} {{ number_format($displayPrice, 2) }}
                @if ($pricingRegion)
                    <span class="ml-1 text-[10px] font-normal uppercase text-gray-400" title="Price for {{ $pricingRegion }}">{{ $pricingRegion }}</span>
                @endif
            </p>
            <span class="mt-3 block w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-center text-sm font-semibold text-white transition group-hover:bg-indigo-500">
                View Course
            </span>
        </div>
    </div>
</a>
