<x-layouts.admin :title="'Add Level'">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Add Level</h1>

    <form method="POST" action="{{ route('admin.levels.store') }}" class="mt-6 max-w-lg space-y-4">
        @csrf
        @include('admin.levels._form', ['level' => null])

        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Create Level</button>
    </form>
</x-layouts.admin>
