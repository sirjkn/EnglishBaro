<x-layouts.student :title="'Support'">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Support</h1>
        <a href="{{ route('student.support.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">New Ticket</a>
    </div>

    @if (session('status'))
        <div class="mt-4 rounded-md bg-green-50 p-3 text-sm text-green-700 dark:bg-green-900/30 dark:text-green-300">
            {{ session('status') }}
        </div>
    @endif

    <div class="mt-6">
        @if ($tickets->isEmpty())
            <x-empty-state message="You haven't submitted any support tickets yet." />
        @else
            <div class="divide-y divide-gray-100 rounded-xl border border-black/10 bg-white dark:divide-gray-700 dark:border-white/10 dark:bg-gray-800">
                @foreach ($tickets as $ticket)
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
                    <a href="{{ route('student.support.show', $ticket) }}" class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $ticket->subject }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $ticket->last_reply_at?->diffForHumans() }}</p>
                        </div>
                        <x-badge :color="$statusColor">{{ $statusLabel }}</x-badge>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.student>
