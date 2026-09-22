<x-layouts.admin :title="'Courses'">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Courses</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.students.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">Students</a>
            <a href="{{ route('admin.levels.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">Show Levels</a>
            <a href="{{ route('admin.courses.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Add Course</a>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card label="Total Courses" :value="$overview['total']" />
        <x-stat-card label="Published Courses" :value="$overview['published']" />
        <x-stat-card label="Total Enrollments" :value="$overview['enrollments']" />
    </div>

    <form method="GET" class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-4">
        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search courses..." class="rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
        <select name="level" class="rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            <option value="">All Levels</option>
            @foreach ($levels as $level)
                <option value="{{ $level->id }}" @selected(($filters['level'] ?? null) == $level->id)>{{ $level->name }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            <option value="">All Statuses</option>
            <option value="draft" @selected(($filters['status'] ?? '') === 'draft')>Draft</option>
            <option value="published" @selected(($filters['status'] ?? '') === 'published')>Published</option>
            <option value="archived" @selected(($filters['status'] ?? '') === 'archived')>Archived</option>
        </select>
        <button type="submit" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200">Filter</button>
    </form>

    <div class="mt-6 overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
            <thead>
                <tr class="bg-indigo-100 text-left text-xs uppercase text-indigo-900 dark:bg-indigo-950 dark:text-indigo-200 divide-x divide-white">
                    <th class="px-4 py-2">Code</th>
                    <th class="px-4 py-2">Course</th>
                    <th class="px-4 py-2">Level</th>
                    <th class="px-4 py-2">Price</th>
                    <th class="px-4 py-2">Students</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Created</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-indigo-100 dark:divide-gray-800 dark:bg-gray-800">
                @forelse ($courses as $course)
                    <tr class="odd:bg-white even:bg-indigo-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                        <td class="px-4 py-3 font-mono text-xs text-gray-500 dark:text-gray-400">{{ $course->course_code ?? '—' }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $course->title }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $course->level?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $course->currency }} {{ number_format((float) $course->price, 2) }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $course->students_count }}</td>
                        <td class="px-4 py-3">
                            <x-badge :color="$course->status === 'published' ? 'green' : ($course->status === 'draft' ? 'yellow' : 'gray')">{{ ucfirst($course->status) }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $course->created_at->format('M d, Y') }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('courses.show', $course) }}" target="_blank" class="text-sm font-medium text-gray-500 hover:text-gray-700">View</a>
                            <a href="{{ route('admin.courses.edit', $course) }}" class="ml-3 text-sm font-medium text-indigo-600 hover:text-indigo-500">Edit</a>
                            <form method="POST" action="{{ route('admin.courses.destroy', $course) }}" class="inline" onsubmit="return confirm('Delete this course? This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="ml-3 text-sm font-medium text-red-600 hover:text-red-500">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No courses found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $courses->links() }}</div>
</x-layouts.admin>
