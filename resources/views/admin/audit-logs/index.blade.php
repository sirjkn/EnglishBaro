<x-layouts.admin :title="'Audit Logs'">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Audit Logs</h1>

    @if ($logs->isEmpty())
        <x-empty-state class="mt-6" message="No audit log entries yet." />
    @else
        <div class="mt-6 overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs uppercase text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <th class="px-4 py-2">User</th>
                        <th class="px-4 py-2">Action</th>
                        <th class="px-4 py-2">Entity</th>
                        <th class="px-4 py-2">IP</th>
                        <th class="px-4 py-2">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-gray-800">
                    @foreach ($logs as $log)
                        <tr>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $log->user?->name ?? 'System' }}</td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $log->action }}</td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ class_basename($log->entity_type) }} #{{ $log->entity_id }}</td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $log->ip_address }}</td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $log->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $logs->links() }}</div>
    @endif
</x-layouts.admin>
