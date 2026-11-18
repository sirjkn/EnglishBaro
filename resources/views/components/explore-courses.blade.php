@props(['courses', 'pricingRegion' => null])

<section id="explore-course" {{ $attributes->merge(['class' => 'scroll-mt-20']) }}>
    <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Explore Courses</h2>
    <p class="mt-2 text-gray-600 dark:text-gray-300">
        One payment per course unlocks every track inside it — the placement test decides where you start.
    </p>

    @if ($courses->isEmpty())
        <x-empty-state class="mt-10" message="No courses available yet." />
    @else
        <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($courses as $course)
                <x-course-card :course="$course" :pricing-region="$pricingRegion" />
            @endforeach
        </div>
    @endif
</section>
