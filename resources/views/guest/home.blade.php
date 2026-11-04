<x-layouts.site :title="'Home'">
    {{-- Hero: a single intro-video slide, attached from Admin → Settings --}}
    <section class="mx-auto max-w-7xl px-4 pt-8 sm:px-6 lg:px-8">
        @if ($introVideo)
            <div class="overflow-hidden rounded-3xl shadow-xl">
                <x-video-player :media="$introVideo" />
            </div>
        @else
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-600 via-indigo-700 to-indigo-900 shadow-xl">
                <div class="relative flex min-h-[320px] flex-col items-start justify-center p-8 sm:min-h-[400px] sm:p-14">
                    <h1 class="max-w-xl text-3xl font-extrabold leading-tight text-white sm:text-4xl lg:text-5xl">
                        Learn English your way
                    </h1>
                    <p class="mt-3 max-w-xl text-sm leading-6 text-indigo-100 sm:text-base">
                        Video lessons, eBooks, and assessments across every level, from Beginner to Advanced.
                    </p>
                    <a href="{{ route('tracks.index') }}" class="mt-6 flex items-center gap-2 rounded-lg bg-amber-400 px-5 py-2.5 text-sm font-semibold text-indigo-950 shadow-sm hover:bg-amber-300">
                        Browse Courses
                        <x-icons.arrow-right class="h-4 w-4" />
                    </a>
                </div>
            </div>
        @endif
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
