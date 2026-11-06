<x-layouts.site :title="$course->name" :description="Str::limit(strip_tags($course->description), 150)">
    {{-- Breadcrumb --}}
    <div class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
        <nav class="flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="flex items-center hover:text-indigo-600 dark:hover:text-indigo-400">
                <x-icons.home class="h-4 w-4" />
                <span class="sr-only">Home</span>
            </a>
            <svg class="h-3.5 w-3.5 flex-none text-gray-300 dark:text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 6 6 6-6 6" /></svg>
            <span class="font-medium text-gray-900 dark:text-white">{{ $course->name }}</span>
        </nav>
    </div>

    {{-- Hero --}}
    <div class="mx-auto max-w-7xl px-4 pb-6 pt-6 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-10 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <div class="aspect-[16/10] w-full overflow-hidden rounded-2xl border border-gray-200 bg-gradient-to-br from-indigo-100 to-indigo-300 shadow-sm dark:border-gray-700 dark:from-indigo-900 dark:to-indigo-700">
                    @if ($course->thumbnail?->resolved_url)
                        <img src="{{ $course->thumbnail->resolved_url }}" alt="{{ $course->name }}" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full w-full items-center justify-center">
                            <span class="text-8xl font-black text-indigo-700 dark:text-indigo-200">{{ $course->course_code }}</span>
                        </div>
                    @endif
                </div>

                <h1 class="mt-6 text-2xl font-bold text-gray-900 dark:text-white">{{ $course->name }}</h1>
                <div class="prose prose-sm dark:prose-invert mt-3 max-w-none">
                    <p>{{ $course->description }}</p>
                    @if ($course->learning_outcomes)
                        <h3>What You'll Achieve</h3>
                        <p>{{ $course->learning_outcomes }}</p>
                    @endif
                </div>
            </div>

            <div>
                <div class="relative z-10 rounded-2xl border border-black/10 bg-white p-6 shadow-md dark:border-gray-700 dark:bg-gray-900 lg:sticky lg:top-24">
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">
                        {{ $course->currency }} {{ number_format($course->priceForRegion($pricingRegion), 2) }}
                    </p>
                    @if ($pricingRegion)
                        <p class="mt-1 text-xs text-gray-400">Pricing shown for your region: {{ $pricingRegion }}</p>
                    @endif
                    <dl class="mt-4 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                        <div class="flex justify-between"><dt>Units</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $course->tracks->count() }}</dd></div>
                        <div class="flex justify-between"><dt>Subscription</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $course->subscription_days }} days</dd></div>
                    </dl>
                    <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                        One payment unlocks every unit in {{ $course->name }}. The placement test decides which unit you start on — you then progress through every unit above it automatically as you complete each one.
                    </p>

                    <div class="mt-6 space-y-2">
                        @auth
                            @if (auth()->user()->isAdmin())
                                <a href="{{ route('admin.courses.edit', $course) }}" class="block w-full rounded-md bg-indigo-600 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-indigo-500">
                                    Edit Course
                                </a>
                                <a href="{{ route('admin.courses.index') }}" class="block w-full rounded-md border border-gray-300 px-4 py-2.5 text-center text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">
                                    Back to Courses
                                </a>
                            @elseif ($isEnrolled)
                                <a href="{{ route('student.tracks.show', $course->defaultTrack()) }}" class="block w-full rounded-md bg-indigo-600 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-indigo-500">
                                    {{ $hasStartedLearning ? 'Continue Learning' : 'Start Learning' }}
                                </a>
                            @elseif (! auth()->user()->studentProfile?->track_id)
                                <a href="{{ route('student.placement-test') }}" class="block w-full rounded-md bg-indigo-600 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-indigo-500">
                                    Take Placement Test to Enroll
                                </a>
                                <p class="text-center text-xs text-gray-500 dark:text-gray-400">
                                    New students take a quick placement test first so we can enroll you into the right unit.
                                </p>
                            @elseif (auth()->user()->can('enroll', $course))
                                <x-payment-method-modal :course="$course" label="Enroll" class="block w-full rounded-md bg-indigo-600 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-indigo-500" />
                            @else
                                <button type="button" disabled class="block w-full cursor-not-allowed rounded-md bg-gray-200 px-4 py-2.5 text-center text-sm font-semibold text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                                    Not Available
                                </button>
                            @endif
                        @else
                            <a href="{{ route('login', ['redirect' => route('courses.show', $course, absolute: false)]) }}" class="block w-full rounded-md bg-indigo-600 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-indigo-500">
                                Login to Enroll
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Units --}}
    <div class="mx-auto max-w-7xl border-t border-gray-200 px-4 py-12 dark:border-gray-800 sm:px-6 lg:px-8">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Units in this Course</h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
            Every unit has 100 levels of Grammar, Listening, Speaking and Reading. Buying {{ $course->name }} unlocks all of them — the placement test decides which one you start on.
        </p>

        @if ($course->tracks->isEmpty())
            <x-empty-state class="mt-8" message="No units published yet." />
        @else
            <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($course->tracks as $track)
                    <x-track-card :track="$track" />
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.site>
