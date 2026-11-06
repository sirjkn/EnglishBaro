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

    <div class="mt-8 grid grid-cols-1 gap-5 lg:grid-cols-2" x-data="{ open: null }">
        @forelse ($courses as $course)
            <div>
                <div class="flex flex-wrap items-center justify-between gap-1.5 rounded-t-lg border border-b-0 border-indigo-200 bg-indigo-50 px-3 py-2 dark:border-indigo-900 dark:bg-indigo-950/40">
                    <div class="flex items-center gap-1.5">
                        <span class="rounded-full bg-indigo-600 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white">{{ $course->course_code }}</span>
                        <h3 class="text-xs font-bold text-gray-900 dark:text-white">{{ $course->name }}</h3>
                        <span class="text-[11px] text-gray-500 dark:text-gray-400">{{ $course->currency }} {{ number_format($course->defaultPrice(), 2) }} &middot; {{ $course->subscription_days }}d</span>
                    </div>
                    <span class="text-[11px] font-semibold text-indigo-700 dark:text-indigo-300">{{ $courseEnrollmentCounts[$course->id] ?? 0 }} enrolled</span>
                </div>

                <div class="overflow-x-auto rounded-b-lg border border-gray-200 dark:border-gray-700">
                    <table class="min-w-full divide-y divide-gray-200 text-[11px] dark:divide-gray-700">
                        <thead>
                            <tr class="bg-indigo-600 text-left text-[10px] uppercase text-white dark:bg-indigo-800 dark:text-white divide-x divide-white">
                                <th class="px-2.5 py-1.5">Unit</th>
                                <th class="px-2.5 py-1.5">Levels</th>
                                <th class="px-2.5 py-1.5">Enrolled</th>
                                <th class="px-2.5 py-1.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-indigo-100 dark:divide-gray-800 dark:bg-gray-800">
                            @forelse ($course->tracks as $track)
                                <tr class="odd:bg-white even:bg-indigo-50 dark:odd:bg-gray-800 dark:even:bg-gray-900 divide-x divide-gray-200 dark:divide-gray-700">
                                    <td class="px-2.5 py-1.5 font-medium text-gray-900 dark:text-white">{{ $track->track_code }} &middot; {{ $track->name }}</td>
                                    <td class="px-2.5 py-1.5 text-gray-700 dark:text-gray-300">{{ $track->levels_count ?? $track->levels()->count() }}</td>
                                    <td class="px-2.5 py-1.5 text-gray-700 dark:text-gray-300">{{ $track->enrollments_count }}</td>
                                    <td class="px-2.5 py-1.5 text-right">
                                        <button type="button" @click="open = {{ $track->id }}" class="text-[11px] font-medium text-indigo-600 hover:text-indigo-500">
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
            <p class="rounded-xl border border-gray-200 px-4 py-8 text-center text-gray-500 dark:border-gray-700 dark:text-gray-400 lg:col-span-2">
                No courses yet.
            </p>
        @endforelse
    </div>
</x-layouts.admin>
