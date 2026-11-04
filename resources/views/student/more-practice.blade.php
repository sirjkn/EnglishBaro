<x-layouts.student :title="'More Practice'">
    <div class="max-w-4xl">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">More practice</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Extra practice at your level, whenever you want it. None of this counts towards your course progress.
        </p>

        <div class="mt-4 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-300">
            <span class="font-bold">Nothing here changes your course level.</span>
            Only the first placement test decides that. If you want a different level, change it yourself from My Course.
        </div>

        @php
            $categories = [
                ['icon' => 'pencil', 'title' => 'Grammar Practice', 'description' => 'Extra exercises on the grammar you have already studied.'],
                ['icon' => 'music-note', 'title' => 'Listening Practice', 'description' => 'Short recordings with questions. Play them as often as you like.'],
                ['icon' => 'book-open', 'title' => 'Reading Practice', 'description' => 'Notices, letters and messages with comprehension questions.'],
                ['icon' => 'microphone', 'title' => 'Speaking Practice', 'description' => 'Situations to respond to, with practice questions.'],
            ];
        @endphp

        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
            @foreach ($categories as $category)
                <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        <x-dynamic-component :component="'icons.'.$category['icon']" class="h-5 w-5" />
                    </span>

                    <h2 class="mt-3 text-base font-bold text-gray-900 dark:text-white">{{ $category['title'] }}</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $category['description'] }}</p>

                    <div class="mt-4 flex items-center justify-between">
                        @if ($levelLabel)
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-500 dark:bg-gray-700 dark:text-gray-400">{{ $levelLabel }} level</span>
                        @else
                            <span></span>
                        @endif

                        <button type="button" disabled title="Coming soon" class="cursor-not-allowed rounded-full bg-teal-700/60 px-4 py-1.5 text-xs font-semibold text-white">
                            Start practice
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.student>
