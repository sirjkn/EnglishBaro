<x-layouts.site :title="'Home'">
    {{-- Hero --}}
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
        <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2">
            <div>
                <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl dark:text-white">
                    Master English with a personal online tutor
                </h1>
                <p class="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-300">
                    Video lessons, downloadable eBooks, and real assessments — everything you need to speak, write,
                    and think confidently in English, at your own pace.
                </p>
                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="{{ route('courses.index') }}" class="rounded-md bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                        Explore Courses
                    </a>
                    <a href="{{ route('register.create') }}" class="rounded-md border border-gray-300 px-6 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">
                        Join EnglishBaro
                    </a>
                </div>
            </div>
            <div class="flex justify-center">
                <div class="aspect-square w-full max-w-md rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 shadow-xl flex items-center justify-center">
                    <span class="text-8xl">📚</span>
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
                        <p class="mt-4 text-sm font-semibold text-gray-900 dark:text-white">{{ $testimonial->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $testimonial->role_label }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.site>
