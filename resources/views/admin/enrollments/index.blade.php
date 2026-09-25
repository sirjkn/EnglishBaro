<x-layouts.admin :title="'Enrollments'">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Enrollments</h1>
    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Currently enrolled courses (tracks). Click a track to see its enrolled students.</p>

    <div class="mt-6 overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700" x-data="{ open: null }">
        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
            <thead>
                <tr class="bg-indigo-600 text-left text-xs uppercase text-white dark:bg-indigo-800 dark:text-white divide-x divide-white">
                    <th class="px-4 py-2">Track</th>
                    <th class="px-4 py-2">Level Count</th>
                    <th class="px-4 py-2">Price</th>
                    <th class="px-4 py-2">Enrolled Students</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-indigo-100 dark:divide-gray-800 dark:bg-gray-800">
                @forelse ($tracks as $track)
                    <tr class="odd:bg-white even:bg-indigo-50 dark:odd:bg-gray-800 dark:even:bg-gray-900 divide-x divide-gray-200 dark:divide-gray-700">
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $track->track_code }} &middot; {{ $track->name }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $track->levels_count ?? $track->levels()->count() }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $track->currency }} {{ number_format($track->defaultPrice(), 2) }}</td>
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
                                        {{ $track->track_code }} &middot; {{ $track->name }} &mdash; Enrolled Students
                                    </h2>
                                    <button type="button" @click="open = null" class="rounded-md px-2 py-1 text-xl leading-none text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                                        &times;
                                    </button>
                                </div>

                                <dl class="mt-3 flex flex-wrap gap-x-6 gap-y-1 text-sm text-gray-600 dark:text-gray-400">
                                    <div><dt class="inline font-medium">Levels:</dt> <dd class="inline">{{ $track->levels_count ?? $track->levels()->count() }}</dd></div>
                                    <div><dt class="inline font-medium">Price:</dt> <dd class="inline">{{ $track->currency }} {{ number_format($track->defaultPrice(), 2) }}</dd></div>
                                    <div><dt class="inline font-medium">Subscription:</dt> <dd class="inline">{{ $track->subscription_days }} days</dd></div>
                                </dl>

                                <div class="mt-4 overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                                        <thead>
                                            <tr class="bg-indigo-600 text-left text-xs uppercase text-white dark:bg-indigo-800 dark:text-white divide-x divide-white">
                                                <th class="px-3 py-2">Student</th>
                                                <th class="px-3 py-2">Enrolled</th>
                                                <th class="px-3 py-2">Progress</th>
                                                <th class="px-3 py-2">Expires</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-indigo-100 dark:divide-gray-800 dark:bg-gray-800">
                                            @forelse ($track->enrollments as $enrollment)
                                                <tr class="odd:bg-white even:bg-indigo-50 dark:odd:bg-gray-800 dark:even:bg-gray-900 divide-x divide-gray-200 dark:divide-gray-700">
                                                    <td class="px-3 py-2">
                                                        <p class="font-medium text-gray-900 dark:text-white">{{ $enrollment->user?->name }}</p>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $enrollment->user?->email }}</p>
                                                    </td>
                                                    <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ $enrollment->enrolled_at?->format('M d, Y') }}</td>
                                                    <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ $enrollment->progress?->percent_complete ?? 0 }}%</td>
                                                    <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ $enrollment->subscription?->expires_at?->format('M d, Y') ?? '—' }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="4" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">No students enrolled yet.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </template>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No tracks yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.admin>
