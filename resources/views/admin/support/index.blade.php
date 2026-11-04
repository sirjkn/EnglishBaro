<x-layouts.admin :title="'Support'">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Support</h1>
    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Tickets submitted by students.</p>

    @if (session('status'))
        <div class="mt-4 rounded-md bg-green-50 p-3 text-sm text-green-700 dark:bg-green-900/30 dark:text-green-300">
            {{ session('status') }}
        </div>
    @endif

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card label="Waiting for Answer" :value="$overview['waiting']" icon="clock" color="rose" />
        <x-stat-card label="Answered" :value="$overview['answered']" icon="user-check" color="emerald" />
        <x-stat-card label="Closed" :value="$overview['closed']" icon="clipboard-check" color="purple" />
    </div>

    <div class="mt-6 flex gap-2">
        <a href="{{ route('admin.support.index') }}" class="rounded-md px-3 py-1.5 text-sm font-medium {{ ! ($filters['status'] ?? null) ? 'bg-indigo-600 text-white' : 'border border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200' }}">All</a>
        <a href="{{ route('admin.support.index', ['status' => 'waiting_for_answer']) }}" class="rounded-md px-3 py-1.5 text-sm font-medium {{ ($filters['status'] ?? null) === 'waiting_for_answer' ? 'bg-indigo-600 text-white' : 'border border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200' }}">Waiting for Answer</a>
        <a href="{{ route('admin.support.index', ['status' => 'answered']) }}" class="rounded-md px-3 py-1.5 text-sm font-medium {{ ($filters['status'] ?? null) === 'answered' ? 'bg-indigo-600 text-white' : 'border border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200' }}">Answered</a>
        <a href="{{ route('admin.support.index', ['status' => 'closed']) }}" class="rounded-md px-3 py-1.5 text-sm font-medium {{ ($filters['status'] ?? null) === 'closed' ? 'bg-indigo-600 text-white' : 'border border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200' }}">Closed</a>
    </div>

    <div class="mt-4 overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 text-xs dark:divide-gray-700">
            <thead>
                <tr class="bg-indigo-600 text-left text-xs uppercase text-white dark:bg-indigo-800 dark:text-white divide-x divide-white">
                    <th class="px-4 py-2">Subject</th>
                    <th class="px-4 py-2">Student</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Last Activity</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-indigo-100 dark:divide-gray-800">
                @forelse ($tickets as $ticket)
                    @php
                        $statusColor = match ($ticket->status) {
                            \App\Models\SupportTicket::ANSWERED => 'green',
                            \App\Models\SupportTicket::CLOSED => 'black',
                            default => 'red',
                        };
                        $statusLabel = match ($ticket->status) {
                            \App\Models\SupportTicket::ANSWERED => 'Answered',
                            \App\Models\SupportTicket::CLOSED => 'Closed',
                            default => 'Waiting for Answer',
                        };
                    @endphp
                    <tr class="odd:bg-white even:bg-indigo-50 dark:odd:bg-gray-800 dark:even:bg-gray-900 divide-x divide-gray-200 dark:divide-gray-700">
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                            <a href="{{ route('admin.support.show', $ticket) }}" class="hover:text-indigo-600 hover:underline">{{ $ticket->subject }}</a>
                        </td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                            <a href="{{ route('admin.students.show', $ticket->user) }}" class="text-indigo-600 hover:text-indigo-500 hover:underline dark:text-indigo-400">{{ $ticket->user->name }}</a>
                        </td>
                        <td class="px-4 py-3"><x-badge :color="$statusColor">{{ $statusLabel }}</x-badge></td>
                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $ticket->last_reply_at?->diffForHumans() }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.support.show', $ticket) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No support tickets.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $tickets->links() }}</div>
</x-layouts.admin>
