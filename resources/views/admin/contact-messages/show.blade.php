<x-layouts.admin :title="$message->subject">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $message->subject }}</h1>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        From {{ $message->full_name }} &lt;{{ $message->email }}&gt;
        @if ($message->phone) &middot; {{ $message->phone }} @endif
        &middot; {{ $message->created_at->format('M d, Y H:i') }}
    </p>

    <div class="mt-6 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <p class="whitespace-pre-line text-sm text-gray-700 dark:text-gray-300">{{ $message->message }}</p>
    </div>

    <form method="POST" action="{{ route('admin.contact-messages.update', $message) }}" class="mt-6 flex items-center gap-3">
        @csrf
        @method('PUT')
        <select name="status" class="rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            @foreach (['new','read','replied','archived'] as $status)
                <option value="{{ $status }}" @selected($message->status === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Update Status</button>
        <a href="mailto:{{ $message->email }}?subject=RE: {{ $message->subject }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">Reply via Email</a>
    </form>
</x-layouts.admin>
