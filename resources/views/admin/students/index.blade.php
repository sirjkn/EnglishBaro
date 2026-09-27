<x-layouts.admin :title="'Students'">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Students</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.tracks.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">Tracks</a>
            <a href="{{ route('admin.students.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Add Student</a>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card label="Total Students" :value="$overview['total']" />
        <x-stat-card label="Active Students" :value="$overview['active']" />
        <x-stat-card label="New This Month" :value="$overview['new_this_month']" />
    </div>

    <form method="GET" class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-4">
        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search name, email, student ID..." class="rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
        <select name="track" class="rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            <option value="">All Tracks</option>
            @foreach ($tracks as $track)
                <option value="{{ $track->id }}" @selected(($filters['track'] ?? null) == $track->id)>{{ $track->track_code }} — {{ $track->name }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            <option value="">All Statuses</option>
            <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
            <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
        </select>
        <button type="submit" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200">Filter</button>
    </form>

    <div class="mt-6 overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
            <thead>
                <tr class="bg-indigo-600 text-left text-xs uppercase text-white dark:bg-indigo-800 dark:text-white divide-x divide-white">
                    <th class="px-4 py-2">Student</th>
                    <th class="px-4 py-2">Student ID</th>
                    <th class="px-4 py-2">Email</th>
                    <th class="px-4 py-2">Track</th>
                    <th class="px-4 py-2">Enrollments</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-indigo-100 dark:divide-gray-800 dark:bg-gray-800">
                @forelse ($students as $student)
                    <tr class="odd:bg-white even:bg-indigo-50 dark:odd:bg-gray-800 dark:even:bg-gray-900 divide-x divide-gray-200 dark:divide-gray-700">
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $student->name }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $student->studentProfile?->student_id }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $student->email }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $student->studentProfile?->track?->track_code ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $student->enrollments_count }}</td>
                        <td class="px-4 py-3">
                            <x-badge :color="$student->is_active ? 'green' : 'gray'">{{ $student->is_active ? 'Active' : 'Inactive' }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.students.show', $student) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">View</a>
                            <a href="{{ route('admin.students.edit', $student) }}" class="ml-3 text-sm font-medium text-gray-600 hover:text-gray-800 dark:text-gray-300">Edit</a>
                            <form method="POST" action="{{ route('admin.students.destroy', $student) }}" class="inline" onsubmit="return confirm('Remove this student? This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="ml-3 text-sm font-medium text-red-600 hover:text-red-500">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No students found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $students->links() }}</div>
</x-layouts.admin>
