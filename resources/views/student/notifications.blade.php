<x-layouts.student :title="'Notifications'">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Notifications</h1>
        <form method="POST" action="{{ route('student.notifications.read-all') }}">
            @csrf
            <button type="submit" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">Mark all as read</button>
        </form>
    </div>

    @if ($notifications->isEmpty())
        <x-empty-state class="mt-6" message="No notifications yet." />
    @else
        <div class="mt-6 divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white dark:divide-gray-700 dark:border-gray-700 dark:bg-gray-800">
            @foreach ($notifications as $notification)
                <div class="flex items-center justify-between p-4">
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $notification->title }}</p>
                        @if ($notification->body)
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $notification->body }}</p>
                        @endif
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    @unless ($notification->read_at)
                        <form method="POST" action="{{ route('student.notifications.read', $notification) }}">
                            @csrf
                            <button type="submit" class="text-xs font-medium text-indigo-600 hover:text-indigo-500">Mark read</button>
                        </form>
                    @endunless
                </div>
            @endforeach
        </div>

        <div class="mt-4">{{ $notifications->links() }}</div>
    @endif
</x-layouts.student>
