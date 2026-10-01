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
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Featured Tracks</h2>
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
    <section class="bg-gray-50 dark:bg-gray-800/50 py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 class="text-center text-2xl font-bold text-gray-900 dark:text-white">How It Works</h2>
            <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-5">
                @foreach ([
                    ['icon' => '🔍', 'title' => 'Identify a Track'],
                    ['icon' => '👤', 'title' => 'Create an Account'],
                    ['icon' => '💳', 'title' => 'Pay For Your '.$stats['access_days'].' Days Subscription'],
                    ['icon' => '🎓', 'title' => 'Start Learning'],
                    ['icon' => '🏆', 'title' => 'Earn a Certificate'],
                ] as $index => $step)
                    <div class="text-center">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-indigo-600 text-2xl text-white">
                            {{ $step['icon'] }}
                        </div>
                        <p class="mt-3 text-sm font-medium text-gray-900 dark:text-white">{{ $index + 1 }}. {{ $step['title'] }}</p>
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
</x-layouts.site>
