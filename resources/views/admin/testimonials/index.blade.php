<x-layouts.admin :title="'Testimonials'">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Testimonials</h1>
        <a href="{{ route('admin.testimonials.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Add Testimonial</a>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card label="All Testimonials" :value="$overview['total']" icon="medal" color="indigo" />
        <x-stat-card label="Average Rating" :value="number_format($overview['average_rating'], 1).' / 5'" icon="star" color="amber" />
        <x-stat-card label="Published vs Unpublished" :value="$overview['published'].' / '.$overview['unpublished']" icon="user-check" color="emerald" />
    </div>

    <div class="mt-6 overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 text-xs dark:divide-gray-700">
            <thead>
                <tr class="bg-indigo-600 text-left text-xs uppercase text-white dark:bg-indigo-800 dark:text-white divide-x divide-white">
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Role</th>
                    <th class="px-4 py-2">Rating</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-indigo-100 dark:divide-gray-800 dark:bg-gray-800">
                @forelse ($testimonials as $testimonial)
                    <tr class="odd:bg-white even:bg-indigo-50 dark:odd:bg-gray-800 dark:even:bg-gray-900 divide-x divide-gray-200 dark:divide-gray-700">
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                            @if ($testimonial->user_id && $testimonial->user?->user_type === 'student')
                                <a href="{{ route('admin.students.show', $testimonial->user) }}" class="text-indigo-600 hover:text-indigo-500 hover:underline dark:text-indigo-400">{{ $testimonial->name }}</a>
                            @else
                                {{ $testimonial->name }}
                            @endif
                        </td>
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
