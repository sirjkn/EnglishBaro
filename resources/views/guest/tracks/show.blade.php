<x-layouts.site :title="$track->track_code.' - '.$track->name" :description="Str::limit(strip_tags($track->description), 150)">
    {{-- Breadcrumb --}}
    <div class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
        <nav class="flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="flex items-center hover:text-indigo-600 dark:hover:text-indigo-400">
                <x-icons.home class="h-4 w-4" />
                <span class="sr-only">Home</span>
            </a>
            <svg class="h-3.5 w-3.5 flex-none text-gray-300 dark:text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 6 6 6-6 6" /></svg>
            <a href="{{ route('tracks.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Categories</a>
            <svg class="h-3.5 w-3.5 flex-none text-gray-300 dark:text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 6 6 6-6 6" /></svg>
            <a href="{{ route('tracks.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Language</a>
            <svg class="h-3.5 w-3.5 flex-none text-gray-300 dark:text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 6 6 6-6 6" /></svg>
            <a href="{{ route('tracks.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">English</a>
            <svg class="h-3.5 w-3.5 flex-none text-gray-300 dark:text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 6 6 6-6 6" /></svg>
            <span class="font-medium text-gray-900 dark:text-white">{{ $track->track_code }}</span>
        </nav>
    </div>

    {{-- Hero --}}
    <div class="mx-auto max-w-7xl px-4 pb-6 pt-6 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-10 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <div class="aspect-[16/10] w-full overflow-hidden rounded-2xl border border-gray-200 bg-gradient-to-br from-indigo-100 to-indigo-300 shadow-sm dark:border-gray-700 dark:from-indigo-900 dark:to-indigo-700">
                    @if ($track->thumbnail?->resolved_url)
                        <img src="{{ $track->thumbnail->resolved_url }}" alt="{{ $track->name }}" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full w-full items-center justify-center">
                            <span class="text-8xl font-black text-indigo-700 dark:text-indigo-200">{{ $track->track_code }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <div>
                <div class="relative z-10 rounded-2xl border border-gray-200 bg-white p-6 shadow-md dark:border-gray-700 dark:bg-gray-900 lg:sticky lg:top-24">
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">
                        {{ $track->currency }} {{ number_format($track->priceForRegion($pricingRegion), 2) }}
                    </p>
                    @if ($pricingRegion)
                        <p class="mt-1 text-xs text-gray-400">Pricing shown for your region: {{ $pricingRegion }}</p>
                    @endif
                    <dl class="mt-4 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                        <div class="flex justify-between"><dt>Track</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $track->track_code }}</dd></div>
                        <div class="flex justify-between"><dt>Levels</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $levels->total() }}</dd></div>
                        <div class="flex justify-between"><dt>Lessons</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $totalLessons }}</dd></div>
                        <div class="flex justify-between"><dt>Subscription</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $track->subscription_days }} days</dd></div>
                    </dl>
                    <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                        One payment unlocks all {{ $levels->total() }} levels of this track. The next track is purchased separately.
                    </p>

                    <div class="mt-6 space-y-2">
                        @auth
                            @if (auth()->user()->isAdmin())
                                <a href="{{ route('admin.tracks.edit', $track) }}" class="block w-full rounded-md bg-indigo-600 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-indigo-500">
                                    Edit Track
                                </a>
                                <a href="{{ route('admin.tracks.index') }}" class="block w-full rounded-md border border-gray-300 px-4 py-2.5 text-center text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">
                                    Back to Track List
                                </a>
                            @elseif ($isEnrolled)
                                <a href="{{ route('student.tracks.show', $track) }}" class="block w-full rounded-md bg-indigo-600 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-indigo-500">
                                    Continue Learning
                                </a>
                            @elseif (! auth()->user()->studentProfile?->track_id)
                                <a href="{{ route('student.placement-test') }}" class="block w-full rounded-md bg-indigo-600 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-indigo-500">
                                    Take Placement Test to Enroll
                                </a>
                                <p class="text-center text-xs text-gray-500 dark:text-gray-400">
                                    New students take a quick placement test first so we can enroll you into the right track.
                                </p>
                            @elseif (auth()->user()->can('enroll', $track))
                                <a href="{{ route('dashboard') }}" class="block w-full rounded-md bg-indigo-600 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-indigo-500">
                                    Enroll / Pay
                                </a>
                            @else
                                <button type="button" disabled class="block w-full cursor-not-allowed rounded-md bg-gray-200 px-4 py-2.5 text-center text-sm font-semibold text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                                    Track Locked
                                </button>
                                <p class="text-center text-xs text-gray-500 dark:text-gray-400">
                                    You were placed into {{ auth()->user()->studentProfile->track->track_code }}. You can enroll in {{ auth()->user()->studentProfile->track->track_code }} or any track below it.
                                </p>
                            @endif
                        @else
                            <a href="{{ route('register.create', ['redirect' => route('tracks.show', $track, absolute: false)]) }}" class="block w-full rounded-md bg-indigo-600 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-indigo-500">
                                Join to Enroll
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Section nav with scroll-spy --}}
    <div
        class="sticky top-0 z-20 border-y border-gray-200 bg-white/90 backdrop-blur dark:border-gray-800 dark:bg-gray-900/90"
        x-data="{ active: 'about' }"
        x-init="
            const observer = new IntersectionObserver(
                (entries) => entries.forEach((entry) => { if (entry.isIntersecting) { active = entry.target.id; } }),
                { rootMargin: '-60px 0px -70% 0px', threshold: 0 }
            );
            ['about', 'levels', 'testimonials', 'subscription'].forEach((id) => {
                const el = document.getElementById(id);
                if (el) observer.observe(el);
            });
        "
    >
        <div class="mx-auto flex max-w-7xl justify-center gap-8 overflow-x-auto px-4 sm:px-6 lg:px-8">
            <a href="#about" :class="active === 'about' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-600 dark:text-gray-300'" class="whitespace-nowrap border-b-2 py-3 text-sm font-semibold hover:border-indigo-600 hover:text-indigo-600">About</a>
            <a href="#levels" :class="active === 'levels' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-600 dark:text-gray-300'" class="whitespace-nowrap border-b-2 py-3 text-sm font-semibold hover:border-indigo-600 hover:text-indigo-600">Levels</a>
            <a href="#testimonials" :class="active === 'testimonials' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-600 dark:text-gray-300'" class="whitespace-nowrap border-b-2 py-3 text-sm font-semibold hover:border-indigo-600 hover:text-indigo-600">Testimonials</a>
            <a href="#subscription" :class="active === 'subscription' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-600 dark:text-gray-300'" class="whitespace-nowrap border-b-2 py-3 text-sm font-semibold hover:border-indigo-600 hover:text-indigo-600">Subscription</a>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        {{-- About --}}
        <section id="about" class="scroll-mt-16 border-b border-gray-200 py-12 dark:border-gray-800">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">About this course</h2>
            <div class="prose prose-sm dark:prose-invert mt-4 max-w-none">
                <p>{{ $track->description }}</p>

                @if ($track->learning_outcomes)
                    <h3>What You'll Achieve</h3>
                    <p>{{ $track->learning_outcomes }}</p>
                @endif
            </div>

            <div class="mt-8 rounded-xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-700 dark:bg-gray-800">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Certificate</h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                    Complete every level in this track to earn a verifiable EnglishBaro certificate.
                </p>
            </div>
        </section>

        {{-- Levels --}}
        <section id="levels" class="scroll-mt-16 border-b border-gray-200 py-12 dark:border-gray-800" x-data="{ expanded: false }">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Levels</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                Each level contains Grammar, Listening, Speaking and Reading sections.
            </p>

            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                @forelse ($levels as $index => $level)
                    <a
                        href="{{ route('tracks.level', [$track, $level]) }}"
                        @if ($index >= 6) x-show="expanded" x-cloak @endif
                        class="rounded-lg border border-gray-200 p-4 transition hover:border-indigo-400 dark:border-gray-700"
                    >
                        <p class="font-medium text-gray-900 dark:text-white">{{ $level->title }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ $level->sections->map(fn ($section) => $section->title)->implode(' · ') }}
                        </p>
                    </a>
                @empty
                    <x-empty-state message="Track content is being prepared." />
                @endforelse
            </div>

            @if ($levels->count() > 6)
                <button
                    type="button"
                    @click="expanded = !expanded"
                    class="mt-5 inline-flex items-center gap-1 text-sm font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400"
                >
                    <span x-text="expanded ? 'Show less' : 'Show more levels'"></span>
                    <svg class="h-4 w-4 transition-transform" :class="{ 'rotate-180': expanded }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6" /></svg>
                </button>
            @endif

            @if ($levels->hasPages())
                <div class="mt-6">{{ $levels->links() }}</div>
            @endif
        </section>

        {{-- Testimonials --}}
        <section id="testimonials" class="scroll-mt-16 border-b border-gray-200 py-12 dark:border-gray-800">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">What our students say</h2>

            @php
                $testimonials = \App\Models\Testimonial::query()
                    ->where('is_published', true)
                    ->with('avatar')
                    ->orderBy('order')
                    ->take(6)
                    ->get();
            @endphp

            @if ($testimonials->isEmpty())
                <div class="mt-4">
                    <x-empty-state message="No testimonials yet." />
                </div>
            @else
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($testimonials as $testimonial)
                        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                            @if ($testimonial->rating)
                                <div class="flex gap-0.5 text-amber-400">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <svg class="h-4 w-4 {{ $i <= $testimonial->rating ? 'fill-current' : 'fill-gray-200 text-gray-200 dark:fill-gray-700 dark:text-gray-700' }}" viewBox="0 0 20 20"><path d="M10 1.5l2.6 5.27 5.82.85-4.21 4.1 1 5.8L10 14.9l-5.21 2.74 1-5.8-4.21-4.1 5.82-.85L10 1.5z" /></svg>
                                    @endfor
                                </div>
                            @endif
                            <p class="mt-3 text-sm italic text-gray-600 dark:text-gray-300">&ldquo;{{ $testimonial->content }}&rdquo;</p>
                            <div class="mt-4 flex items-center gap-3">
                                <div class="flex h-9 w-9 flex-none items-center justify-center overflow-hidden rounded-full bg-indigo-100 text-sm font-semibold text-indigo-700 dark:bg-indigo-900 dark:text-indigo-200">
                                    @if ($testimonial->avatar?->resolved_url)
                                        <img src="{{ $testimonial->avatar->resolved_url }}" alt="{{ $testimonial->name }}" class="h-full w-full object-cover">
                                    @else
                                        {{ Str::substr($testimonial->name, 0, 1) }}
                                    @endif
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $testimonial->name }}</p>
                                    @if ($testimonial->role_label)
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $testimonial->role_label }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Subscription --}}
        <section id="subscription" class="scroll-mt-16 py-12">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Subscription terms</h2>
            <div class="prose prose-sm dark:prose-invert mt-4 max-w-none">
                <ul>
                    <li>One payment unlocks every level of the {{ $track->track_code }} track for {{ $track->subscription_days }} days from the date of purchase.</li>
                    <li>Access to this track is separate from other tracks — each track is purchased and subscribed to individually.</li>
                    <li>Pricing is shown in {{ $track->currency }} and may vary by region.</li>
                    <li>Your subscription covers all video lessons, eBooks, assessments and the end-of-track certificate for this track.</li>
                </ul>
            </div>
        </section>
    </div>
</x-layouts.site>
