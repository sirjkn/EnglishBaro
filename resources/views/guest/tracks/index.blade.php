<x-layouts.site :title="'Explore Course Tracks'">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Explore Course Tracks</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-300">
            Four tracks, 100 levels each. One payment unlocks every level in a track.
        </p>

        <form method="GET" action="{{ route('tracks.index') }}" class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="lg:col-span-2">
                <label for="search" class="sr-only">Search</label>
                <input type="text" name="search" id="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search tracks..."
                       class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>

            <div>
                <label for="sort" class="sr-only">Sort</label>
                <select name="sort" id="sort" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="order" @selected(($filters['sort'] ?? 'order') === 'order')>Track Order</option>
                    <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>Price: Low to High</option>
                    <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>Price: High to Low</option>
                    <option value="name" @selected(($filters['sort'] ?? '') === 'name')>Name A-Z</option>
                </select>
            </div>

            <div>
                <button type="submit" class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">
                    Filter
                </button>
            </div>
        </form>

        @if ($tracks->isEmpty())
            <x-empty-state class="mt-10" message="No tracks match your filters." />
        @else
            <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($tracks as $track)
                    <x-track-card :track="$track" :pricing-region="$pricingRegion" />
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.site>
