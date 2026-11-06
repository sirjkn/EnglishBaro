<x-layouts.admin :title="'Students'">
    @if (session('status'))
        <div class="rounded-md bg-green-50 p-3 text-sm text-green-700 dark:bg-green-900/30 dark:text-green-300">
            {{ session('status') }}
        </div>
    @endif

    <div
        class="mt-4 grid grid-cols-1 gap-6 lg:grid-cols-2"
        x-data="{ selected: {{ $openStudentId ?? 'null' }}, mode: '{{ $openStudentId ? 'edit' : 'create' }}' }"
    >
        {{-- 1/2: overview, Add Student, student list --}}
        <div>
            <div class="grid grid-cols-3 gap-2">
                <x-stat-card label="Total Students" :value="$overview['total']" compact class="!p-2" label-size="text-[9px]" />
                <x-stat-card label="Active Students" :value="$overview['active']" compact class="!p-2" label-size="text-[9px]" />
                <x-stat-card label="New This Month" :value="$overview['new_this_month']" compact class="!p-2" label-size="text-[9px]" />
            </div>

            <button
                type="button"
                @click="mode = 'create'; selected = null"
                class="mt-4 block w-full rounded-md bg-gradient-to-r from-indigo-600 to-indigo-500 px-4 py-2 text-center text-sm font-semibold text-white shadow-sm hover:from-indigo-500 hover:to-indigo-400"
            >
                + Add Student
            </button>

            <div class="mt-4 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="bg-indigo-600 px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-white dark:bg-indigo-800">
                    Students
                </div>
                <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($students as $student)
                        <li>
                            <button
                                type="button"
                                @click="mode = 'edit'; selected = {{ $student->id }}"
                                class="w-full px-3 py-2.5 text-left transition"
                                :class="(mode === 'edit' && selected === {{ $student->id }}) ? 'bg-indigo-50 dark:bg-indigo-900/30' : 'bg-white hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700'"
                            >
                                <div class="flex items-center justify-between gap-2">
                                    <span class="truncate text-xs font-semibold text-gray-900 dark:text-white">{{ $student->name }}</span>
                                    <x-badge :color="$student->is_active ? 'green' : 'gray'">{{ $student->is_active ? 'Active' : 'Inactive' }}</x-badge>
                                </div>
                                <p class="mt-1 truncate text-[11px] text-gray-500 dark:text-gray-400">
                                    {{ $student->studentProfile?->student_id }} &middot; {{ $student->studentProfile?->track?->track_code ?? 'No track' }} &middot; {{ $student->enrollments_count }} enroll.
                                </p>
                            </button>
                        </li>
                    @empty
                        <li class="px-3 py-8 text-center text-xs text-gray-400">No students found.</li>
                    @endforelse
                </ul>
            </div>

            <div class="mt-3">{{ $students->links() }}</div>
        </div>

        {{-- 1/2: filters + inline create/edit panel --}}
        <div>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Students</h1>
                <a href="{{ route('admin.tracks.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">All Units (Tracks)</a>
            </div>

            <form method="GET" class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
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

            <div class="mt-6">
                {{-- Create panel --}}
                <div x-show="mode === 'create'" x-cloak>
                    <div class="rounded-t-xl bg-indigo-600 px-4 py-3 dark:bg-indigo-800">
                        <h2 class="text-sm font-bold text-white">Add a new student</h2>
                    </div>
                    <div class="rounded-b-xl border border-gray-200 p-4 dark:border-gray-700">
                        <form method="POST" action="{{ route('admin.students.store') }}" enctype="multipart/form-data">
                            @csrf
                            @include('admin.students._create-fields', ['countries' => $countries])
                            <button type="submit" class="mt-6 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Create Student</button>
                        </form>
                    </div>
                </div>

                {{-- Edit panels, one per student on this page --}}
                @forelse ($students as $student)
                    <div x-show="mode === 'edit' && selected === {{ $student->id }}" x-cloak>
                        <div class="flex flex-wrap items-center justify-between gap-2 rounded-t-xl bg-indigo-600 px-4 py-3 dark:bg-indigo-800">
                            <h2 class="text-sm font-bold text-white">Editing: {{ $student->name }}</h2>
                            <div class="flex items-center gap-3">
                                <a href="{{ route('admin.students.show', $student) }}" class="text-xs font-medium text-indigo-100 hover:text-white">Full Profile</a>
                                <form method="POST" action="{{ route('admin.students.destroy', $student) }}" class="inline" onsubmit="return confirm('Remove this student? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-medium text-red-200 hover:text-white">Delete</button>
                                </form>
                            </div>
                        </div>

                        <div class="rounded-b-xl border border-gray-200 p-4 dark:border-gray-700">
                            <form method="POST" action="{{ route('admin.students.update', $student) }}" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="redirect" value="{{ request()->fullUrlWithQuery(['open' => $student->id]) }}">
                                @include('admin.students._edit-fields', ['student' => $student, 'tracks' => $tracks, 'countries' => $countries])
                                <button type="submit" class="mt-6 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save Changes</button>
                            </form>
                        </div>
                    </div>
                @empty
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.admin>
