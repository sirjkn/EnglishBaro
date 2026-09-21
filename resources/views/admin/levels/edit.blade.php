<x-layouts.admin :title="'Edit Level'">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Level</h1>

    <form method="POST" action="{{ route('admin.levels.update', $level) }}" class="mt-6 max-w-lg space-y-4">
        @csrf
        @method('PUT')
        @include('admin.levels._form', ['level' => $level])

        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save Changes</button>
    </form>
</x-layouts.admin>
