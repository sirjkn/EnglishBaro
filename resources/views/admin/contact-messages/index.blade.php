<x-layouts.admin :title="'Contact Messages'">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Contact Messages</h1>

    <form method="GET" class="mt-6 flex gap-3">
        <select name="status" class="rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            <option value="">All Statuses</option>
            @foreach (['new','read','replied','archived'] as $status)
                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200">Filter</button>
    </form>

    <div class="mt-6 overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
            <thead>
                <tr class="bg-indigo-900 text-left text-xs uppercase text-white dark:bg-indigo-950 divide-x divide-white/40">
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Subject</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Received</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-indigo-100 dark:divide-gray-800 dark:bg-gray-800">
                @forelse ($messages as $message)
                    <tr class="odd:bg-white even:bg-indigo-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $message->full_name }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $message->subject }}</td>
                        <td class="px-4 py-3">
                            <x-badge :color="$message->status === 'new' ? 'indigo' : ($message->status === 'archived' ? 'gray' : 'green')">{{ ucfirst($message->status) }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $message->created_at->format('M d, Y') }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.contact-messages.show', $message) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">View</a>
                            <form method="POST" action="{{ route('admin.contact-messages.destroy', $message) }}" class="inline" onsubmit="return confirm('Delete this message?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="ml-3 text-sm font-medium text-red-600 hover:text-red-500">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No messages found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $messages->links() }}</div>
</x-layouts.admin>
