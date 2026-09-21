<x-layouts.site :title="'Explore Courses'">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Explore Courses</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-300">Find the right English course for your level and goals.</p>

        <form method="GET" action="{{ route('courses.index') }}" class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div class="lg:col-span-2">
                <label for="search" class="sr-only">Search</label>
                <input type="text" name="search" id="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search courses..."
                       class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>

            <div>
                <label for="level" class="sr-only">Level</label>
                <select name="level" id="level" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">All Levels</option>
                    @foreach ($levels as $level)
                        <option value="{{ $level->id }}" @selected(($filters['level'] ?? null) == $level->id)>{{ $level->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="sort" class="sr-only">Sort</label>
                <select name="sort" id="sort" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="newest" @selected(($filters['sort'] ?? 'newest') === 'newest')>Newest</option>
                    <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>Price: Low to High</option>
                    <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>Price: High to Low</option>
                    <option value="title" @selected(($filters['sort'] ?? '') === 'title')>Title A-Z</option>
                </select>
            </div>

            <div>
                <button type="submit" class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">
                    Filter
                </button>
            </div>
        </form>

        @if ($courses->isEmpty())
            <x-empty-state class="mt-10" message="No courses match your filters." />
        @else
            <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($courses as $course)
                    <x-course-card :course="$course" />
                @endforeach
            </div>

            <div class="mt-10">
                {{ $courses->links() }}
            </div>
        @endif
    </div>
</x-layouts.site>
