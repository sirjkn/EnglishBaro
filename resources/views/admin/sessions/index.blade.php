<x-layouts.admin :title="'Sessions'">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Active Sessions</h1>

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

    <div class="mt-6 overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
            <thead>
                <tr class="bg-indigo-600 text-left text-xs uppercase text-white dark:bg-indigo-800 dark:text-white divide-x divide-white">
                    <th class="px-4 py-2">User</th>
                    <th class="px-4 py-2">Device</th>
                    <th class="px-4 py-2">Browser</th>
                    <th class="px-4 py-2">IP</th>
                    <th class="px-4 py-2">Last Activity</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-indigo-100 dark:divide-gray-800 dark:bg-gray-800">
                @forelse ($sessions as $session)
                    <tr class="odd:bg-white even:bg-indigo-50 dark:odd:bg-gray-800 dark:even:bg-gray-900 divide-x divide-gray-200 dark:divide-gray-700">
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                            {{ $session->user->name }}
                            @if ($session->user->studentProfile?->student_id)
                                <span class="block text-xs text-gray-400 dark:text-gray-500">{{ $session->user->studentProfile->student_id }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $session->device }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $session->browser }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $session->ip_address }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $session->last_activity_at?->diffForHumans() }}</td>
                        <td class="px-4 py-3"><x-badge :color="$session->status === 'active' ? 'green' : 'gray'">{{ ucfirst($session->status) }}</x-badge></td>
                        <td class="px-4 py-3 text-right">
                            @if ($session->status === 'active')
                                <form method="POST" action="{{ route('admin.sessions.destroy', $session) }}" onsubmit="return confirm('Terminate this session?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-500">Terminate</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No sessions found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $sessions->links() }}</div>
</x-layouts.admin>
