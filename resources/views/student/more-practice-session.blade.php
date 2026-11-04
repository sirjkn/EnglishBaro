<x-layouts.student :title="$meta['title']">
    <div class="max-w-2xl">
        <a href="{{ route('student.more-practice') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
            &larr; Back to More Practice
        </a>

        <div class="mt-4 rounded-xl border border-gray-200 bg-white p-8 text-center dark:border-gray-700 dark:bg-gray-800">
            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-lg bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                <x-dynamic-component :component="'icons.'.$meta['icon']" class="h-6 w-6" />
            </span>

            <h1 class="mt-4 text-xl font-bold text-gray-900 dark:text-white">{{ $meta['title'] }}</h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $meta['description'] }}</p>
            @if ($levelLabel)
                <p class="mt-3 inline-block rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-500 dark:bg-gray-700 dark:text-gray-400">{{ $levelLabel }} level</p>
            @endif

            <p class="mt-6 text-sm font-medium text-gray-700 dark:text-gray-300">
                We're still building the question set for this practice set — check back soon.
            </p>
        </div>
    </div>
</x-layouts.student>
