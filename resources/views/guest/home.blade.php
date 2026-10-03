<x-layouts.site :title="'Home'">
    {{-- Hero: full-bleed, auto-advancing "most popular" showcase --}}
    <section class="mx-auto max-w-7xl px-4 pt-8 sm:px-6 lg:px-8">
        @if ($popularTracks->isNotEmpty())
            <div
                x-data="{
                    active: 0,
                    count: {{ $popularTracks->count() }},
                    timer: null,
                    reduceMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
                    start() {
                        if (this.reduceMotion) return;
                        this.stop();
                        this.timer = setInterval(() => { this.active = (this.active + 1) % this.count; }, 2000);
                    },
                    stop() { clearInterval(this.timer); },
                }"
                x-init="start()"
                @mouseenter="stop()"
                @mouseleave="start()"
                class="relative overflow-hidden rounded-3xl shadow-xl"
            >
                <div class="relative h-[420px] sm:h-[460px] lg:h-[520px]">
                    @foreach ($popularTracks as $index => $track)
                        <div
                            x-show="active === {{ $index }}"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="absolute inset-0"
                        >
                            @if ($track->thumbnail?->resolved_url)
                                <img src="{{ $track->thumbnail->resolved_url }}" alt="{{ $track->name }}" class="h-full w-full object-cover">
                            @else
                                <div class="h-full w-full bg-gradient-to-br from-indigo-600 via-indigo-700 to-indigo-900"></div>
                            @endif

                            <div class="absolute inset-0 bg-gradient-to-t from-indigo-950/90 via-indigo-950/30 to-transparent"></div>

                            <div class="absolute inset-x-0 bottom-0 p-6 sm:p-10 lg:p-14">
                                <span class="inline-flex items-center rounded-full bg-amber-400 px-3 py-1 text-xs font-bold uppercase tracking-wide text-indigo-950">
                                    Most Popular
                                </span>
                                <h1 class="mt-4 max-w-xl text-3xl font-extrabold leading-tight text-white sm:text-4xl lg:text-5xl">
                                    {{ $track->name }}
                                </h1>
                                <p class="mt-2 max-w-xl text-sm leading-6 text-indigo-100 sm:text-base">
                                    {{ Str::limit(strip_tags($track->description), 130) }}
                                </p>
                                <div class="mt-6 flex flex-wrap items-center gap-4">
                                    <a href="{{ route('tracks.show', $track) }}" class="flex items-center gap-2 rounded-lg bg-amber-400 px-5 py-2.5 text-sm font-semibold text-indigo-950 shadow-sm hover:bg-amber-300">
                                        Explore {{ $track->track_code }}
                                        <x-icons.arrow-right class="h-4 w-4" />
                                    </a>
                                    <span class="text-sm font-semibold text-white">
                                        {{ $track->currency }} {{ number_format($track->priceForRegion($pricingRegion), 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Dot navigation --}}
                <div class="absolute inset-x-0 bottom-4 flex justify-center gap-2 sm:bottom-6">
                    @foreach ($popularTracks as $index => $track)
                        <button
                            type="button"
                            @click="active = {{ $index }}; stop(); start()"
                            :class="active === {{ $index }} ? 'w-6 bg-white' : 'w-2 bg-white/40 hover:bg-white/70'"
                            class="h-2 rounded-full transition-all"
                            aria-label="Show {{ $track->name }}"
                        ></button>
                    @endforeach
                </div>
            </div>
        @endif
    </section>

    {{-- Featured tracks --}}
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Featured Courses</h2>
            <a href="{{ route('tracks.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">View all &rarr;</a>
        </div>

        @if ($featuredTracks->isEmpty())
            <x-empty-state class="mt-8" message="No featured tracks yet. Check back soon." />
        @else
            <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($featuredTracks as $track)
                    <x-featured-track-card :track="$track" :pricing-region="$pricingRegion" />
                @endforeach
            </div>
        @endif
    </section>

    {{-- How it works --}}
    <section class="bg-gray-50 py-16 dark:bg-gray-800/50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-center gap-4">
                <span class="hidden h-px w-12 bg-indigo-200 dark:bg-indigo-800 sm:block"></span>
                <h2 class="text-center text-3xl font-extrabold text-gray-900 dark:text-white">How It Works</h2>
                <span class="hidden h-px w-12 bg-indigo-200 dark:bg-indigo-800 sm:block"></span>
            </div>
            <p class="mt-2 text-center text-sm text-gray-500 dark:text-gray-400">
                Your learning journey, simplified in 5 easy steps.
            </p>

            <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-5">
                @foreach ([
                    [
                        'icon' => 'search',
                        'title' => 'Identify a Track',
                        'description' => 'Explore our tracks and choose the one that matches your level and goals.',
                        'image' => asset('images/how-it-works/identify-track.png'),
                    ],
                    [
                        'icon' => 'user-plus',
                        'title' => 'Create an Account',
                        'description' => 'Sign up in minutes and get instant access to your personal learning dashboard.',
                        'image' => asset('images/how-it-works/create-account.png'),
                    ],
                    [
                        'icon' => 'credit-card',
                        'title' => 'Pay For Your '.$stats['access_days'].' Days Subscription',
                        'description' => 'Choose your preferred payment method and activate your '.$stats['access_days'].'-day access.',
                        'image' => asset('images/how-it-works/pay-subscription.png'),
                    ],
                    [
                        'icon' => 'play-circle',
                        'title' => 'Start Learning',
                        'description' => 'Dive into video lessons, eBooks and assessments at your own pace.',
                        'image' => asset('images/how-it-works/start-learning.png'),
                    ],
                    [
                        'icon' => 'medal',
                        'title' => 'Earn a Certificate',
                        'description' => 'Complete every level in your track and receive a verifiable certificate.',
                        'image' => asset('images/how-it-works/earn-certificate.png'),
                    ],
                ] as $index => $step)
                    <div class="flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-lg shadow-gray-200/60 transition hover:-translate-y-1 hover:shadow-xl dark:border-gray-700 dark:bg-gray-800 dark:shadow-black/30">
                        <div class="p-5">
                            <div class="flex items-center gap-2">
                                <span class="flex h-8 w-8 flex-none items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white">
                                    {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                </span>
                                <span class="flex h-8 w-8 flex-none items-center justify-center rounded-full bg-indigo-100 text-indigo-600 dark:bg-indigo-900 dark:text-indigo-300">
                                    <x-dynamic-component :component="'icons.' . $step['icon']" class="h-4 w-4" />
                                </span>
                            </div>
                            <p class="mt-4 text-base font-bold text-gray-900 dark:text-white">{{ $step['title'] }}</p>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $step['description'] }}</p>
                        </div>

                        <div class="mt-auto p-3 pt-0">
                            <div class="aspect-[4/3] w-full overflow-hidden rounded-xl bg-indigo-50 dark:bg-indigo-950">
                                <img src="{{ $step['image'] }}" alt="{{ $step['title'] }}" class="h-full w-full object-cover">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Reviews --}}
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

    {{-- FAQ --}}
    <section id="faq" class="scroll-mt-20 bg-gray-50 py-16 dark:bg-gray-800/50">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="text-center text-2xl font-bold text-gray-900 dark:text-white">Frequently Asked Questions</h2>
            <p class="mt-2 text-center text-sm text-gray-500 dark:text-gray-400">
                Can't find what you're looking for? <a href="{{ route('contact') }}" class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">Contact us</a>.
            </p>

            <div class="mt-10 space-y-3">
                @foreach ([
                    [
                        'q' => 'How do tracks and levels work?',
                        'a' => 'Each track (A1 through C2, plus our language tracks) is broken into 100 levels, and every level covers Grammar, Listening, Speaking and Reading. You move through levels in order, one lesson at a time.',
                    ],
                    [
                        'q' => 'Which track should I start with?',
                        'a' => 'New students take a short placement test after signing up, and we automatically enroll you into the right starting track based on your result.',
                    ],
                    [
                        'q' => 'What does one payment unlock?',
                        'a' => 'A single payment unlocks every level of that specific track for the subscription period shown on the track page (120 days by default). Other tracks are purchased separately.',
                    ],
                    [
                        'q' => 'What happens when my subscription ends?',
                        'a' => "You'll keep any certificates you've already earned, but you'll need to renew to continue accessing new lessons in that track.",
                    ],
                    [
                        'q' => 'Do I get a certificate?',
                        'a' => 'Yes — completing every level in a track earns you a verifiable EnglishBaro certificate for that track.',
                    ],
                    [
                        'q' => 'Can I access EnglishBaro on my phone?',
                        'a' => 'Yes, the platform works in any modern mobile browser, so you can watch lessons, read eBooks and take assessments from your phone or tablet.',
                    ],
                    [
                        'q' => 'How many times can I log in?',
                        'a' => 'Student accounts are limited to 3 logins per month to help keep accounts personal and secure. This resets automatically each month.',
                    ],
                ] as $index => $faq)
                    <div
                        x-data="{ open: {{ $index === 0 ? 'true' : 'false' }} }"
                        class="rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800"
                    >
                        <button
                            type="button"
                            @click="open = !open"
                            class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left"
                        >
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $faq['q'] }}</span>
                            <svg class="h-4 w-4 flex-none text-gray-400 transition-transform" :class="{ 'rotate-180': open }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6" /></svg>
                        </button>
                        <div
                            x-show="open"
                            x-cloak
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            class="px-5 pb-4 text-sm text-gray-600 dark:text-gray-300"
                        >
                            {{ $faq['a'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</x-layouts.site>
