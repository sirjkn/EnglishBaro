<x-layouts.admin :title="'Levels'">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Levels</h1>
        <a href="{{ route('admin.levels.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Add Level</a>
    </div>

    <div class="mt-6 overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
            <thead>
                <tr class="bg-indigo-900 text-left text-xs uppercase text-white dark:bg-indigo-950 divide-x divide-white/40">
                    <th class="px-4 py-2">Order</th>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Courses</th>
                    <th class="px-4 py-2">Default</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-indigo-100 dark:divide-gray-800 dark:bg-gray-800">
                @foreach ($levels as $level)
                    <tr class="odd:bg-white even:bg-indigo-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $level->order }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $level->name }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $level->courses_count }}</td>
                        <td class="px-4 py-3">
                            @if ($level->is_default)
                                <x-badge color="indigo">Default</x-badge>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <x-badge :color="$level->is_active ? 'green' : 'gray'">{{ $level->is_active ? 'Active' : 'Inactive' }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.levels.edit', $level) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Edit</a>
                            <form method="POST" action="{{ route('admin.levels.destroy', $level) }}" class="inline" onsubmit="return confirm('Delete this level?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="ml-3 text-sm font-medium text-red-600 hover:text-red-500">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-layouts.admin>
