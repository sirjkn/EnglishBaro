@props(['course', 'pricingRegion' => null])

@php
    $displayPrice = $course->priceForRegion($pricingRegion);

    $authUser = auth()->user();
    $defaultTrack = $course->defaultTrack();
    $currentTrack = $authUser?->studentProfile?->track;
    // TrackPolicy::enroll only checks placement order, not whether a
    // payment has actually happened — "enrolled" has to come from a real
    // active Enrollment row, not that policy.
    $isEnrolledInCourse = $authUser && $currentTrack && $currentTrack->course_id === $course->id
        && $authUser->enrollments()->where('track_id', $currentTrack->id)->where('status', 'active')->exists();

    $badge = match (true) {
        $isEnrolledInCourse => 'enrolled',
        $authUser && ! $authUser->can('enroll', $course) => 'locked',
        default => null,
    };

    // The track the student is actively working through in this course —
    // its own progress stands in for "course progress" here, same number
    // shown on the dashboard and My Courses, since a student only ever
    // actively progresses one unit of a course at a time.
    $progress = $isEnrolledInCourse
        ? \App\Models\TrackProgress::query()->where('user_id', $authUser->id)->where('track_id', $currentTrack->id)->first()
        : null;
@endphp

<a href="{{ $isEnrolledInCourse ? route('student.tracks.show', $currentTrack) : route('courses.show', $course) }}" class="group flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800">
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
            @if ($isEnrolledInCourse)
                <div class="flex items-center justify-between text-xs font-medium text-gray-500 dark:text-gray-400">
                    <span>Your progress</span>
                    <span class="text-indigo-600 dark:text-indigo-400">{{ (int) ($progress->percent_complete ?? 0) }}%</span>
                </div>
                <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                    <div class="h-full rounded-full bg-indigo-600" style="width: {{ (float) ($progress->percent_complete ?? 0) }}%"></div>
                </div>
                <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500">
                    {{ $progress->levels_completed ?? 0 }} / {{ $progress->total_levels ?? 0 }} levels &middot; {{ $progress->lessons_completed ?? 0 }} / {{ $progress->total_lessons ?? 0 }} lessons
                </p>
            @else
                <p class="text-sm font-bold text-gray-900 dark:text-white">
                    {{ $course->currency }} {{ number_format($displayPrice, 2) }}
                    @if ($pricingRegion)
                        <span class="ml-1 text-[10px] font-normal uppercase text-gray-400" title="Price for {{ $pricingRegion }}">{{ $pricingRegion }}</span>
                    @endif
                </p>
            @endif
            <span class="mt-3 block w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-center text-sm font-semibold text-white transition group-hover:bg-indigo-500">
                {{ $isEnrolledInCourse ? 'Continue Learning' : 'View Course' }}
            </span>
        </div>
    </div>
</a>
