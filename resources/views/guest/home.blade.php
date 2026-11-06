<x-layouts.site :title="'Home'">
    @if ($dashboardData)
        {{-- A logged-in student's own profile/progress summary, above
        everything else on Landing. --}}
        <section class="mx-auto max-w-7xl px-4 pt-8 sm:px-6 lg:px-8">
            <x-student-dashboard-summary
                :student-profile="$dashboardData['studentProfile']"
                :active-subscription="$dashboardData['activeSubscription']"
                :enrollments="$dashboardData['enrollments']"
                :needs-placement-test="$dashboardData['needsPlacementTest']"
                :upcoming-track="$dashboardData['upcomingTrack']"
                :nearest-expiry="$dashboardData['nearestExpiry']"
                :sessions-this-month="$dashboardData['sessionsThisMonth']"
                :session-monthly-limit="$dashboardData['sessionMonthlyLimit']"
                :latest-session="$dashboardData['latestSession']"
                :paid-currency="$dashboardData['paidCurrency']"
                :total-paid="$dashboardData['totalPaid']"
                :successful-payments-count="$dashboardData['successfulPaymentsCount']"
            />
        </section>
    @endif

    {{-- Explore Courses --}}
    <section id="explore-course" class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Explore Courses</h2>
        <p class="mt-2 text-gray-600 dark:text-gray-300">
            One payment per course unlocks every unit inside it — the placement test decides where you start.
        </p>

        @if ($featuredCourses->isEmpty())
            <x-empty-state class="mt-10" message="No courses available yet." />
        @else
            <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($featuredCourses as $course)
                    <x-course-card :course="$course" :pricing-region="$pricingRegion" />
                @endforeach
            </div>
        @endif
    </section>

    {{-- Reviews: only shown to anonymous visitors deciding whether to sign
    up — a logged-in student landing here from the journey nav has already
    decided, so this social-proof section isn't for them. --}}
    @guest
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <h2 class="text-center text-2xl font-bold text-gray-900 dark:text-white">Why people choose EnglishBaro</h2>

            @if ($testimonials->isEmpty())
                <x-empty-state class="mt-8" message="No reviews yet." />
            @else
                <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($testimonials as $testimonial)
                        <div class="rounded-xl bg-white dark:bg-gray-800 p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                            <div class="text-amber-400">
                                {{ str_repeat('★', $testimonial->rating ?? 5) }}{{ str_repeat('☆', 5 - ($testimonial->rating ?? 5)) }}
                            </div>
                            <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">&ldquo;{{ $testimonial->content }}&rdquo;</p>
                            <div class="mt-4 flex items-center gap-3">
                                @if ($testimonial->avatar?->resolved_url)
                                    <img src="{{ $testimonial->avatar->resolved_url }}" alt="{{ $testimonial->name }}" class="h-10 w-10 rounded-full object-cover">
                                @else
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-600 dark:bg-indigo-900 dark:text-indigo-300">
                                        {{ collect(explode(' ', $testimonial->name))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('') }}
                                    </div>
                                @endif
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $testimonial->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $testimonial->role_label }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    @endguest

</x-layouts.site>
