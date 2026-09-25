<x-layouts.admin :title="'Add Course Track'">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Add Course Track</h1>
        <a href="{{ route('admin.tracks.index') }}" class="rounded-md border border-indigo-600 px-4 py-2 text-sm font-semibold text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/30">
            &larr; Back to Tracks
        </a>
    </div>

    <p class="mt-2 max-w-3xl text-sm text-gray-600 dark:text-gray-400">
        Creating a track automatically generates its full curriculum: 100 levels, each with the four
        Grammar/Listening/Speaking/Reading sections, sample lessons, and Activity Questions. You can edit
        the content afterwards from the track's Levels page.
    </p>

    <form method="POST" action="{{ route('admin.tracks.store') }}" enctype="multipart/form-data" class="mt-6 max-w-3xl">
        @csrf
        @include('admin.tracks._form', ['track' => $track, 'creating' => true])

        <button type="submit" class="mt-6 rounded-md bg-gradient-to-r from-indigo-600 to-indigo-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:from-indigo-500 hover:to-indigo-400">
            Create Track
        </button>
    </form>
</x-layouts.admin>
