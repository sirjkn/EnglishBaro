<x-layouts.admin :title="'Enrollments'">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Enrollments</h1>
    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        Grouped by course — one payment enrolls a student in every unit (track) under that course at once, so
        headcounts are shown per course first, then broken down by unit below.
    </p>

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card label="Total Enrollments" :value="$overview['total']" icon="user-plus" color="indigo" />
        <x-stat-card label="Active Enrollments" :value="$overview['active']" icon="user-check" color="emerald" />
        <x-stat-card label="Cancelled" :value="$overview['cancelled']" icon="clock" color="rose" />
    </div>

    {{-- By-course overview: the sum of enrolled students per course --}}
    <h2 class="mt-8 text-sm font-bold uppercase tracking-wide text-gray-400">Enrolled Students by Course</h2>
    <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @forelse ($courses as $course)
            <x-stat-card :label="$course->name" :value="$courseEnrollmentCounts[$course->id] ?? 0" icon="user-plus" color="indigo" />
        @empty
            <p class="text-sm text-gray-400">No courses yet.</p>
        @endforelse
    </div>

    <div class="mt-8 space-y-8" x-data="{ open: null }">
        @forelse ($courses as $course)
            <div>
                <div class="flex flex-wrap items-center justify-between gap-2 rounded-t-xl border border-b-0 border-indigo-200 bg-indigo-50 px-4 py-3 dark:border-indigo-900 dark:bg-indigo-950/40">
                    <div class="flex items-center gap-2">
                        <span class="rounded-full bg-indigo-600 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-white">{{ $course->course_code }}</span>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ $course->name }}</h3>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $course->currency }} {{ number_format($course->defaultPrice(), 2) }} &middot; {{ $course->subscription_days }} days</span>
                    </div>
                    <span class="text-xs font-semibold text-gray-600 dark:text-gray-300">{{ $courseEnrollmentCounts[$course->id] ?? 0 }} enrolled student(s)</span>
                </div>

                <div class="overflow-x-auto rounded-b-xl border border-gray-200 dark:border-gray-700">
                    <table class="min-w-full divide-y divide-gray-200 text-xs dark:divide-gray-700">
                        <thead>
                            <tr class="bg-indigo-600 text-left text-xs uppercase text-white dark:bg-indigo-800 dark:text-white divide-x divide-white">
                                <th class="px-4 py-2">Unit (Track)</th>
                                <th class="px-4 py-2">Level Count</th>
                                <th class="px-4 py-2">Enrolled Students</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-indigo-100 dark:divide-gray-800 dark:bg-gray-800">
                            @forelse ($course->tracks as $track)
                                <tr class="odd:bg-white even:bg-indigo-50 dark:odd:bg-gray-800 dark:even:bg-gray-900 divide-x divide-gray-200 dark:divide-gray-700">
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $track->track_code }} &middot; {{ $track->name }}</td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $track->levels_count ?? $track->levels()->count() }}</td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $track->enrollments_count }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <button type="button" @click="open = {{ $track->id }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                                            View
                                        </button>
                                    </td>
                                </tr>

                                {{-- Modal --}}
                                <template x-teleport="body">
                                    <div x-show="open === {{ $track->id }}" x-cloak
                                         class="fixed inset-0 z-50 flex items-end justify-center sm:items-center"
                                         style="display: none;">
                                        <div class="fixed inset-0 bg-black/50" @click="open = null"></div>

                                        <div class="relative w-full max-h-[85vh] overflow-y-auto rounded-t-2xl bg-white p-5 shadow-xl sm:max-w-2xl sm:rounded-2xl dark:bg-gray-800"
                                             @click.outside="open = null">
                                            <div class="flex items-center justify-between">
                                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                                    {{ $course->name }}: {{ $track->track_code }} &middot; {{ $track->name }} &mdash; Enrolled Students
                                                </h2>
                                                <button type="button" @click="open = null" class="rounded-md px-2 py-1 text-xl leading-none text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                    &times;
                                                </button>
                                            </div>

                                            <dl class="mt-3 flex flex-wrap gap-x-6 gap-y-1 text-sm text-gray-600 dark:text-gray-400">
                                                <div><dt class="inline font-medium">Course:</dt> <dd class="inline">{{ $course->name }}</dd></div>
                                                <div><dt class="inline font-medium">Levels:</dt> <dd class="inline">{{ $track->levels_count ?? $track->levels()->count() }}</dd></div>
                                                <div><dt class="inline font-medium">Price:</dt> <dd class="inline">{{ $course->currency }} {{ number_format($course->defaultPrice(), 2) }}</dd></div>
                                                <div><dt class="inline font-medium">Subscription:</dt> <dd class="inline">{{ $course->subscription_days }} days</dd></div>
                                            </dl>

                                            <div class="mt-4 overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                                                <table class="min-w-full divide-y divide-gray-200 text-xs dark:divide-gray-700">
                                                    <thead>
                                                        <tr class="bg-indigo-600 text-left text-xs uppercase text-white dark:bg-indigo-800 dark:text-white divide-x divide-white">
                                                            <th class="px-3 py-2">Student</th>
                                                            <th class="px-3 py-2">Enrolled</th>
                                                            <th class="px-3 py-2">Progress</th>
                                                            <th class="px-3 py-2">Expires</th>
                                                            <th class="px-3 py-2">Status</th>
                                                            <th class="px-3 py-2"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-indigo-100 dark:divide-gray-800 dark:bg-gray-800">
                                                        @forelse ($track->enrollments as $enrollment)
                                                            @php
                                                                $enrollmentCancelMessage = "This revokes their access to {$track->track_code}. Their payment record is kept — this does not refund them.";
                                                            @endphp
                                                            <tr class="odd:bg-white even:bg-indigo-50 dark:odd:bg-gray-800 dark:even:bg-gray-900 divide-x divide-gray-200 dark:divide-gray-700">
                                                                <td class="px-3 py-2">
                                                                    @if ($enrollment->user)
                                                                        <a href="{{ route('admin.students.show', $enrollment->user) }}" class="font-medium text-indigo-600 hover:text-indigo-500 hover:underline dark:text-indigo-400">{{ $enrollment->user->name }}</a>
                                                                    @endif
                                                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $enrollment->user?->email }}</p>
                                                                </td>
                                                                <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ $enrollment->enrolled_at?->format('M d, Y') }}</td>
                                                                <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ $enrollment->progress?->percent_complete ?? 0 }}%</td>
                                                                <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ $enrollment->subscription?->expires_at?->format('M d, Y') ?? '—' }}</td>
                                                                <td class="px-3 py-2"><x-badge :color="$enrollment->status === 'active' ? 'green' : 'gray'">{{ ucfirst($enrollment->status) }}</x-badge></td>
                                                                <td class="px-3 py-2 text-right">
                                                                    @if ($enrollment->status === 'active')
                                                                        <x-double-confirm-form
                                                                            :action="route('admin.enrollments.cancel', $enrollment)"
                                                                            label="Cancel"
                                                                            title="Cancel {{ $enrollment->user?->name }}'s enrollment?"
                                                                            :message="$enrollmentCancelMessage"
                                                                            class="text-xs font-medium text-red-600 hover:text-red-500"
                                                                        />
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr><td colspan="6" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">No students enrolled yet.</td></tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            @empty
                                <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400 dark:text-gray-500">No units in this course yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <p class="rounded-xl border border-gray-200 px-4 py-8 text-center text-gray-500 dark:border-gray-700 dark:text-gray-400">
                No courses yet.
            </p>
        @endforelse
    </div>
</x-layouts.admin>
