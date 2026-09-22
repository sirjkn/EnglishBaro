<x-layouts.site :title="$course->title" :description="Str::limit(strip_tags($course->description), 150)">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-10 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <span class="text-xs font-medium uppercase tracking-wide text-indigo-600 dark:text-indigo-400">
                    {{ $course->level?->name ?? 'All Levels' }}
                </span>
                <h1 class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ $course->title }}</h1>

                <div class="mt-4 aspect-video w-full overflow-hidden rounded-xl bg-gradient-to-br from-indigo-100 to-indigo-300 dark:from-indigo-900 dark:to-indigo-700 flex items-center justify-center">
                    @if ($course->thumbnail?->resolved_url)
                        <img src="{{ $course->thumbnail->resolved_url }}" alt="{{ $course->title }}" class="h-full w-full object-cover">
                    @else
                        <span class="text-6xl">🎓</span>
                    @endif
                </div>

                <div class="prose prose-sm dark:prose-invert mt-8 max-w-none">
                    <h2>Description</h2>
                    <p>{{ $course->description }}</p>

                    @if ($course->learning_outcomes)
                        <h2>What You'll Achieve</h2>
                        <p>{{ $course->learning_outcomes }}</p>
                    @endif
                </div>

                <div class="mt-10">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Course Structure</h2>
                    <div class="mt-4 space-y-4">
                        @forelse ($course->sections as $section)
                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                                <h3 class="font-medium text-gray-900 dark:text-white">{{ $section->title }}</h3>
                                <ul class="mt-2 space-y-1 text-sm text-gray-600 dark:text-gray-300">
                                    @foreach ($section->lessons as $lesson)
                                        <li class="flex items-center gap-2">
                                            <span>▸</span> {{ $lesson->title }}
                                            @if ($lesson->is_preview)
                                                <span class="rounded bg-green-100 dark:bg-green-900 px-1.5 py-0.5 text-xs text-green-700 dark:text-green-300">Preview</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @empty
                            <x-empty-state message="Course content is being prepared." />
                        @endforelse
                    </div>
                </div>

                @if ($course->assessments->isNotEmpty())
                    <div class="mt-10">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Assessments</h2>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                            {{ $course->assessments->count() }} assessment(s) included to test your progress.
                        </p>
                    </div>
                @endif

                <div class="mt-10">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Certificate</h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                        Complete all lessons to earn a verifiable EnglishBaro certificate of completion.
                    </p>
                </div>
            </div>

            <div>
                <div class="relative z-10 rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900 p-6 lg:sticky lg:top-24">
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">
                        {{ $course->currency }} {{ number_format($course->priceForRegion($pricingRegion), 2) }}
                    </p>
                    @if ($pricingRegion)
                        <p class="mt-1 text-xs text-gray-400">Pricing shown for your region: {{ $pricingRegion }}</p>
                    @endif
                    <dl class="mt-4 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                        <div class="flex justify-between"><dt>Level</dt><dd>{{ $course->level?->name ?? 'All Levels' }}</dd></div>
                        <div class="flex justify-between"><dt>Subscription</dt><dd>{{ $course->subscription_days }} days</dd></div>
                        <div class="flex justify-between"><dt>Lessons</dt><dd>{{ $totalLessons }}</dd></div>
                    </dl>

                    <div class="mt-6 space-y-2">
                        @auth
                            @if (auth()->user()->isAdmin())
                                <a href="{{ route('admin.courses.edit', $course) }}" class="block w-full rounded-md bg-indigo-600 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-indigo-500">
                                    Edit Course
                                </a>
                                <a href="{{ route('admin.courses.index') }}" class="block w-full rounded-md border border-gray-300 px-4 py-2.5 text-center text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">
                                    Back to Course List
                                </a>
                            @elseif ($isEnrolled)
                                <a href="{{ route('dashboard') }}" class="block w-full rounded-md bg-indigo-600 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-indigo-500">
                                    Continue Learning
                                </a>
                            @else
                                <a href="{{ route('dashboard') }}" class="block w-full rounded-md bg-indigo-600 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-indigo-500">
                                    Enroll / Pay
                                </a>
                            @endif
                        @else
                            <a href="{{ route('register.create', ['redirect' => route('courses.show', $course, absolute: false)]) }}" class="block w-full rounded-md bg-indigo-600 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-indigo-500">
                                Join to Enroll
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.site>
