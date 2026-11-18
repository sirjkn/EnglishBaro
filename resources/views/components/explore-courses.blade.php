@props(['courses', 'pricingRegion' => null])

<section id="explore-course" {{ $attributes->merge(['class' => 'scroll-mt-20']) }}>
    <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">Courses</p>

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
