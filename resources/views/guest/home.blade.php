<x-layouts.site :title="'Home'">
    {{-- Hero --}}
    <section class="mx-auto max-w-7xl px-4 pt-8 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-600 via-indigo-700 to-indigo-900 px-6 py-10 shadow-xl sm:px-10 sm:py-14 lg:px-14">
            <div class="pointer-events-none absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 15% 20%, white 0, transparent 35%), radial-gradient(circle at 85% 15%, white 0, transparent 30%), radial-gradient(circle at 75% 85%, white 0, transparent 35%);"></div>

            <div class="relative max-w-2xl">
                <span class="inline-flex items-center rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-white backdrop-blur">
                    Online English Learning Platform
                </span>

                <h1 class="mt-5 text-3xl font-extrabold leading-tight text-white sm:text-4xl lg:text-5xl">
                    Master English with a personal online tutor
                </h1>

                <p class="mt-4 max-w-xl text-base leading-7 text-indigo-100 sm:text-lg">
                    Video lessons, downloadable eBooks, and real assessments — everything you need to speak, write,
                    and think confidently in English, at your own pace.
                </p>

                <div class="mt-8 border-t border-white/20 pt-6">
                    <div class="flex flex-wrap gap-x-10 gap-y-4">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-indigo-200">Courses</p>
                            <p class="mt-1 flex items-center gap-1.5 text-lg font-bold text-white">
                                <x-icons.book-open class="h-4 w-4 text-amber-300" />
                                {{ $stats['courses'] }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-indigo-200">Video Lessons</p>
                            <p class="mt-1 flex items-center gap-1.5 text-lg font-bold text-white">
                                <x-icons.play-circle class="h-4 w-4 text-amber-300" />
                                {{ $stats['lessons'] }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-indigo-200">Access</p>
                            <p class="mt-1 flex items-center gap-1.5 text-lg font-bold text-white">
                                <x-icons.medal class="h-4 w-4 text-amber-300" />
                                120 Days
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('courses.index') }}" class="flex items-center gap-2 rounded-lg bg-amber-400 px-5 py-2.5 text-sm font-semibold text-indigo-950 shadow-sm hover:bg-amber-300">
                        Explore Courses
                        <x-icons.arrow-right class="h-4 w-4" />
                    </a>
                    <a href="{{ route('register.create') }}" class="flex items-center gap-2 rounded-lg border border-white/30 bg-white/5 px-5 py-2.5 text-sm font-semibold text-white backdrop-blur hover:bg-white/10">
                        <x-icons.map class="h-4 w-4" />
                        Join EnglishBaro
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Feature section --}}
    <section class="bg-gray-50 dark:bg-gray-800/50 py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                <div class="rounded-xl bg-white dark:bg-gray-800 p-6 shadow-sm">
                    <div class="text-3xl">🎥</div>
                    <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">Video Lessons</h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                        Look over the shoulder of your personal tutor - learn visually.
                    </p>
                </div>
                <div class="rounded-xl bg-white dark:bg-gray-800 p-6 shadow-sm">
                    <div class="text-3xl">📖</div>
                    <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">Download eBooks</h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                        For deeper learning download e-books from our resource library.
                    </p>
                </div>
                <div class="rounded-xl bg-white dark:bg-gray-800 p-6 shadow-sm">
                    <div class="text-3xl">📝</div>
                    <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">Online Assessments</h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                        Take tests and quizzes from our huge database of past papers.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Featured courses --}}
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Featured Courses</h2>
            <a href="{{ route('courses.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">View all &rarr;</a>
        </div>

        @if ($featuredCourses->isEmpty())
            <x-empty-state class="mt-8" message="No featured courses yet. Check back soon." />
        @else
            <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($featuredCourses as $course)
                    <x-course-card :course="$course" />
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
                    ['icon' => '🔍', 'title' => 'Identify a Course'],
                    ['icon' => '👤', 'title' => 'Create an Account'],
                    ['icon' => '💳', 'title' => 'Pay For Your 120 Days Subscription'],
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
