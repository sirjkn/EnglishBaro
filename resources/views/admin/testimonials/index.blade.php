<x-layouts.admin :title="'Testimonials'">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Testimonials</h1>
        <a href="{{ route('admin.testimonials.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Add Testimonial</a>
    </div>

    <div class="mt-6 overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
            <thead>
                <tr class="bg-gray-50 text-left text-xs uppercase text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Role</th>
                    <th class="px-4 py-2">Rating</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-gray-800">
                @forelse ($testimonials as $testimonial)
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $testimonial->name }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $testimonial->role_label }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $testimonial->rating }}/5</td>
                        <td class="px-4 py-3">
                            <x-badge :color="$testimonial->is_published ? 'green' : 'gray'">{{ $testimonial->is_published ? 'Published' : 'Unpublished' }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" action="{{ route('admin.testimonials.toggle-publish', $testimonial) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">{{ $testimonial->is_published ? 'Unpublish' : 'Publish' }}</button>
                            </form>
                            <a href="{{ route('admin.testimonials.edit', $testimonial) }}" class="ml-3 text-sm font-medium text-gray-600 hover:text-gray-800 dark:text-gray-300">Edit</a>
                            <form method="POST" action="{{ route('admin.testimonials.destroy', $testimonial) }}" class="inline" onsubmit="return confirm('Delete this testimonial?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="ml-3 text-sm font-medium text-red-600 hover:text-red-500">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No testimonials yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $testimonials->links() }}</div>
</x-layouts.admin>
