<x-layouts.admin :title="'Sessions'">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Active Sessions</h1>
        <div class="flex flex-wrap gap-2">
            <x-confirm-delete-form
                :action="route('admin.sessions.destroy-all-students')"
                label="Terminate All Student Sessions"
                title="Terminate all student sessions?"
                message="This logs out every student currently signed in, site-wide, and frees up their monthly session slot. This cannot be undone."
                class="rounded-md border border-red-300 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-900/30"
            />
            <x-confirm-delete-form
                :action="route('admin.sessions.destroy-all-admins')"
                label="Terminate All Admin Sessions"
                title="Terminate all other admin sessions?"
                message="This logs out every other admin currently signed in, site-wide. Your current session is kept. This cannot be undone."
                class="rounded-md border border-red-300 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-900/30"
            />
        </div>
    </div>

    <form method="GET" class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
        <input type="text" name="student" value="{{ request('student') }}" placeholder="Student name or ID"
               class="rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">

        <input type="text" name="ip" value="{{ request('ip') }}" placeholder="IP address"
               class="rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">

        <select name="device" class="rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            <option value="">All Devices</option>
            @foreach ($devices as $device)
                <option value="{{ $device }}" @selected(request('device') === $device)>{{ $device }}</option>
            @endforeach
        </select>

        <select name="status" class="rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            <option value="">All Statuses</option>
            <option value="active" @selected(request('status') === 'active')>Active</option>
            <option value="terminated" @selected(request('status') === 'terminated')>Terminated</option>
            <option value="expired" @selected(request('status') === 'expired')>Expired</option>
        </select>

        <div class="flex gap-2">
            <button type="submit" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200">Filter</button>
            @if (request()->anyFilled(['student', 'ip', 'device', 'status']))
                <a href="{{ route('admin.sessions.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">Clear</a>
            @endif
        </div>
    </form>

    <div class="mt-4 space-y-2">
        @forelse ($groupedUsers as $group)
            <details class="overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                <summary class="grid cursor-pointer list-none grid-cols-[1fr_5rem_5rem_9rem] items-center gap-3 bg-indigo-50 px-3 py-1.5 leading-tight text-indigo-900 hover:bg-indigo-100 dark:bg-gray-900 dark:text-indigo-200 dark:hover:bg-gray-700/60">
                    <div class="flex min-w-0 items-center gap-2">
                        <svg class="h-3.5 w-3.5 shrink-0 text-indigo-400 transition-transform [details[open]_&]:rotate-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 6 6 6-6 6" /></svg>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium">{{ $group->user?->name ?? 'Deleted user' }}</p>
                            @if ($group->user?->studentProfile?->student_id)
                                <p class="text-[11px] text-indigo-500 dark:text-indigo-400">{{ $group->user->studentProfile->student_id }}</p>
                            @endif
                        </div>
                    </div>
                    <div>
                        @if ($group->activeCount > 0)
                            <span class="rounded-full bg-green-100 px-2 py-0.5 text-[11px] font-semibold text-green-700 dark:bg-green-900/40 dark:text-green-300">{{ $group->activeCount }} active</span>
                        @endif
                    </div>
                    <span class="text-xs text-indigo-500 dark:text-indigo-400">{{ $group->sessions->count() }} session{{ $group->sessions->count() === 1 ? '' : 's' }}</span>
                    <span class="hidden truncate text-xs text-indigo-400 dark:text-indigo-500 sm:inline">last active {{ $group->lastActivityAt?->diffForHumans() }}</span>
                </summary>

                @if ($group->user && $group->activeCount > 1)
                    <div class="flex justify-end border-t border-gray-100 bg-gray-50 px-3 py-1.5 dark:border-gray-700 dark:bg-gray-900">
                        <form method="POST" action="{{ route('admin.sessions.destroy-others', $group->user) }}" onsubmit="return confirm('Terminate all of {{ $group->user->name }}\'s other sessions? The session you\'re currently using (if any) is kept.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-500">Terminate All Except Current</button>
                        </form>
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                        <thead>
                            <tr class="bg-indigo-50 text-left text-xs uppercase text-indigo-700 dark:bg-gray-900 dark:text-indigo-300 divide-x divide-white dark:divide-gray-700">
                                <th class="px-4 py-1.5">Device</th>
                                <th class="px-4 py-1.5">Browser</th>
                                <th class="px-4 py-1.5">IP / Location</th>
                                <th class="px-4 py-1.5">Last Activity</th>
                                <th class="px-4 py-1.5">Status</th>
                                <th class="px-4 py-1.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-indigo-100 dark:divide-gray-800 dark:bg-gray-800">
                            @php
                                $deviceSeenCounts = $group->sessions->countBy('device_token');
                            @endphp
                            @foreach ($group->sessions as $session)
                                <tr class="odd:bg-white even:bg-indigo-50 dark:odd:bg-gray-800 dark:even:bg-gray-900 divide-x divide-gray-200 dark:divide-gray-700">
                                    <td class="px-4 py-1.5 text-gray-700 dark:text-gray-300">
                                        {{ $session->device }}
                                        @if ($session->device_token)
                                            <span class="block font-mono text-[10px] text-gray-400 dark:text-gray-500" title="Device token: {{ $session->device_token }}">
                                                {{ mb_substr($session->device_token, 0, 8) }}
                                                @if (($deviceSeenCounts[$session->device_token] ?? 0) > 1)
                                                    &middot; <span class="text-indigo-500">returning device</span>
                                                @endif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-1.5 text-gray-700 dark:text-gray-300">{{ $session->browser }}</td>
                                    <td class="px-4 py-1.5 text-gray-700 dark:text-gray-300">
                                        {{ $session->ip_address }}
                                        @if ($session->location)
                                            <span class="block text-[11px] text-gray-400 dark:text-gray-500">{{ $session->location }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-1.5 text-gray-700 dark:text-gray-300">{{ $session->last_activity_at?->diffForHumans() }}</td>
                                    <td class="px-4 py-1.5"><x-badge :color="$session->status === 'active' ? 'green' : 'gray'">{{ ucfirst($session->status) }}</x-badge></td>
                                    <td class="px-4 py-1.5 text-right">
                                        @if ($session->status === 'active')
                                            <form method="POST" action="{{ route('admin.sessions.destroy', $session) }}" onsubmit="return confirm('Terminate this session?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-500">Terminate</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        @empty
            <div class="rounded-xl border border-gray-200 px-4 py-8 text-center text-gray-500 dark:border-gray-700 dark:text-gray-400">No sessions found.</div>
        @endforelse
    </div>

    <div class="mt-4">{{ $groupedUsers->links() }}</div>
</x-layouts.admin>
