<x-layouts.site :title="$track->track_code.' - '.$track->name" :description="Str::limit(strip_tags($track->description), 150)">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-10 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <span class="text-xs font-medium uppercase tracking-wide text-indigo-600 dark:text-indigo-400">
                    Track {{ $track->track_code }}
                </span>
                <h1 class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ $track->name }}</h1>

                <div class="mt-4 aspect-video w-full overflow-hidden rounded-xl bg-gradient-to-br from-indigo-100 to-indigo-300 dark:from-indigo-900 dark:to-indigo-700 flex items-center justify-center">
                    @if ($track->thumbnail?->resolved_url)
                        <img src="{{ $track->thumbnail->resolved_url }}" alt="{{ $track->name }}" class="h-full w-full object-cover">
                    @else
                        <span class="text-7xl font-black text-indigo-700 dark:text-indigo-200">{{ $track->track_code }}</span>
                    @endif
                </div>

                <div class="prose prose-sm dark:prose-invert mt-8 max-w-none">
                    <h2>Description</h2>
                    <p>{{ $track->description }}</p>

                    @if ($track->learning_outcomes)
                        <h2>What You'll Achieve</h2>
                        <p>{{ $track->learning_outcomes }}</p>
                    @endif
                </div>

                <div class="mt-10">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Levels</h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        Each level contains Grammar, Listening, Speaking and Reading sections.
                    </p>

                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @forelse ($levels as $level)
                            <a href="{{ route('tracks.level', [$track, $level]) }}" class="rounded-lg border border-gray-200 p-4 transition hover:border-indigo-400 dark:border-gray-700">
                                <p class="font-medium text-gray-900 dark:text-white">{{ $level->title }}</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $level->sections->map(fn ($section) => $section->title)->implode(' · ') }}
                                </p>
                            </a>
                        @empty
                            <x-empty-state message="Track content is being prepared." />
                        @endforelse
                    </div>

                    <div class="mt-6">{{ $levels->links() }}</div>
                </div>

                <div class="mt-10">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Certificate</h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                        Complete every level in this track to earn a verifiable EnglishBaro certificate.
                    </p>
                </div>
            </div>

            <div>
                <div class="relative z-10 rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900 p-6 lg:sticky lg:top-24">
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">
                        {{ $track->currency }} {{ number_format($track->priceForRegion($pricingRegion), 2) }}
                    </p>
                    @if ($pricingRegion)
                        <p class="mt-1 text-xs text-gray-400">Pricing shown for your region: {{ $pricingRegion }}</p>
                    @endif
                    <dl class="mt-4 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                        <div class="flex justify-between"><dt>Track</dt><dd>{{ $track->track_code }}</dd></div>
                        <div class="flex justify-between"><dt>Levels</dt><dd>{{ $levels->total() }}</dd></div>
                        <div class="flex justify-between"><dt>Lessons</dt><dd>{{ $totalLessons }}</dd></div>
                        <div class="flex justify-between"><dt>Subscription</dt><dd>{{ $track->subscription_days }} days</dd></div>
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
</x-layouts.site>
