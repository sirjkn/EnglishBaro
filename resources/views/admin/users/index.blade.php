<x-layouts.admin :title="'Users'">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Users</h1>
        <a href="{{ route('admin.users.create') }}" class="flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
            <x-icons.user-plus class="h-4 w-4" />
            Add User
        </a>
    </div>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        Admin and staff accounts. Admins always have full access; staff access is limited to the modules granted below. Student accounts are managed separately under Students.
    </p>

    <div class="mt-6 overflow-hidden rounded-xl border border-black/10 bg-white dark:border-white/10 dark:bg-gray-800">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">Modules</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse ($users as $user)
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $user->name }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $user->email }}</td>
                        <td class="px-4 py-3">
                            @if ($user->user_type === 'admin')
                                <x-badge color="indigo">Admin</x-badge>
                            @else
                                <x-badge color="amber">Staff</x-badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">
                            @if ($user->user_type === 'admin')
                                All modules
                            @elseif ($user->permissions->isEmpty())
                                <span class="text-red-500">None granted</span>
                            @else
                                {{ $user->permissions->count() }} module{{ $user->permissions->count() === 1 ? '' : 's' }}
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">Edit</a>
                            @unless ($user->is(auth()->user()))
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="ml-3 inline" onsubmit="return confirm('Remove {{ $user->name }}\'s account?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-500 dark:text-red-400">Delete</button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8">
                            <x-empty-state message="No admin or staff accounts yet." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.admin>
