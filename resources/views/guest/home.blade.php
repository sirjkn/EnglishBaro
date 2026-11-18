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

    {{-- Hero: introduction video --}}
    <section class="bg-gradient-to-b from-indigo-50 to-white dark:from-gray-900 dark:to-gray-900">
        <div class="mx-auto grid max-w-7xl grid-cols-1 items-center gap-8 px-4 py-10 sm:px-6 sm:py-14 lg:grid-cols-5 lg:gap-12 lg:px-8 lg:py-16">
            <div class="order-2 min-w-0 lg:order-1 lg:col-span-2">
                <p class="text-xs font-bold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">Welcome to EnglishBaro</p>
                <h1 class="mt-2 text-3xl font-bold leading-tight text-gray-900 [text-wrap:balance] dark:text-white sm:text-4xl lg:text-5xl">
                    Learn English at your own pace, from where you are.
                </h1>
                <p class="mt-4 max-w-lg text-base text-gray-600 dark:text-gray-300 sm:text-lg">
                    Watch the introduction to see how it works: take a short placement test, start at the right track, and move up level by level.
                </p>
                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    @guest
                        <a href="{{ route('register') }}" class="rounded-md bg-indigo-600 px-5 py-3 text-center text-sm font-semibold text-white hover:bg-indigo-500">Get Started</a>
                    @endguest
                    <a href="#explore-course" class="rounded-md border border-indigo-500 bg-white px-5 py-3 text-center text-sm font-semibold text-indigo-600 hover:bg-indigo-50 dark:border-indigo-400 dark:bg-gray-800 dark:text-indigo-300 dark:hover:bg-indigo-900/30">Explore Courses</a>
                </div>
            </div>

            <div class="order-1 min-w-0 lg:order-2 lg:col-span-3">
                <div class="overflow-hidden rounded-xl shadow-xl ring-1 ring-black/5">
                    <x-video-player :media="$introVideo" placeholder="Introduction video coming soon." />
                </div>
            </div>
        </div>
    </section>

    {{-- Explore Courses --}}
    <section id="explore-course" class="mx-auto max-w-7xl scroll-mt-20 px-4 py-16 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Explore Courses</h2>
        <p class="mt-2 text-gray-600 dark:text-gray-300">
            One payment per course unlocks every track inside it — the placement test decides where you start.
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
