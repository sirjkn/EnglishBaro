<x-layouts.admin :title="$ticket->subject">
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

    <a href="{{ route('admin.support.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">&larr; Back to Support</a>

    <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $ticket->subject }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                From <a href="{{ route('admin.students.show', $ticket->user) }}" class="text-indigo-600 hover:text-indigo-500 hover:underline dark:text-indigo-400">{{ $ticket->user->name }}</a>
                ({{ $ticket->user->studentProfile?->student_id ?? $ticket->user->email }})
            </p>
        </div>
        <div class="flex items-center gap-2">
            <x-badge :color="$statusColor">{{ $statusLabel }}</x-badge>
            @if ($ticket->isClosed())
                <form method="POST" action="{{ route('admin.support.reopen', $ticket) }}">
                    @csrf
                    <button type="submit" class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">Reopen</button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.support.close', $ticket) }}" onsubmit="return confirm('Close this ticket?');">
                    @csrf
                    <button type="submit" class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">Close Ticket</button>
                </form>
            @endif
        </div>
    </div>

    @if (session('status'))
        <div class="mt-4 rounded-md bg-green-50 p-3 text-sm text-green-700 dark:bg-green-900/30 dark:text-green-300">
            {{ session('status') }}
        </div>
    @endif

    <x-chat-doodle-background class="mt-6 space-y-4 p-4">
        @foreach ($ticket->replies as $reply)
            <div class="rounded-xl border p-4 {{ $reply->is_admin_reply ? 'border-indigo-200 bg-indigo-50 dark:border-indigo-900 dark:bg-indigo-950/30' : 'border-gray-200 bg-white dark:border-white/10 dark:bg-gray-800' }}">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ $reply->is_admin_reply ? 'Support Team' : $reply->user->name }}
                    </p>
                    <p class="text-xs text-gray-400">{{ $reply->created_at->format('M d, Y H:i') }}</p>
                </div>
                <p class="mt-2 whitespace-pre-line text-sm text-gray-700 dark:text-gray-300">{{ $reply->message }}</p>
            </div>
        @endforeach
    </x-chat-doodle-background>

    @if (! $ticket->isClosed())
        <form method="POST" action="{{ route('admin.support.reply', $ticket) }}" class="mt-6 rounded-xl border border-black/10 bg-white p-4 dark:border-white/10 dark:bg-gray-800">
            @csrf
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Reply</label>
            <textarea name="message" rows="4" required class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white"></textarea>
            @error('message') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            <button type="submit" class="mt-3 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Send Reply</button>
        </form>
    @else
        <p class="mt-6 text-center text-sm text-gray-400">This ticket is closed. Reopen it to reply.</p>
    @endif
</x-layouts.admin>
