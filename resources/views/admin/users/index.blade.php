<x-layouts.admin :title="'Users'">
    @if (session('status'))
        <div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700 dark:bg-green-900/30 dark:text-green-300">
            {{ session('status') }}
        </div>
    @endif

    <div x-data="{ mode: '{{ $openUserId ? 'edit' : 'list' }}', selected: {{ $openUserId ?? 'null' }} }">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Users</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Admin and staff accounts. Admins always have full access; staff access is limited to the modules granted below. Student accounts are managed separately under Students.
                </p>
            </div>
            <button type="button" @click="mode = 'create'; selected = null" class="flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                <x-icons.user-plus class="h-4 w-4" />
                Add User
            </button>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
            {{-- Left: user list --}}
            <div class="overflow-x-auto rounded-xl border border-black/10 bg-white dark:border-white/10 dark:bg-gray-800">
                <table class="w-full min-w-[640px] text-left text-sm">
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
                            <tr :class="mode === 'edit' && selected === {{ $user->id }} ? 'bg-yellow-100 dark:bg-yellow-900/40' : 'hover:bg-yellow-100 dark:hover:bg-yellow-900/30'">
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
                                    <button type="button" @click="mode = 'edit'; selected = {{ $user->id }}" class="rounded border border-indigo-600 px-2 py-1 text-xs font-medium text-indigo-600 hover:bg-indigo-600 hover:text-white dark:border-indigo-400 dark:text-indigo-400">Edit</button>
                                    @unless ($user->is(auth()->user()))
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="ml-2 inline" onsubmit="return confirm('Remove {{ $user->name }}\'s account?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded border border-red-600 px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-600 hover:text-white dark:border-red-500 dark:text-red-400">Delete</button>
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

            {{-- Right: inline create/edit panel --}}
            <div>
                <div x-show="mode === 'create'" x-cloak>
                    @include('admin.users._form', ['user' => new \App\Models\User(['user_type' => 'staff']), 'modules' => $modules, 'grantedIds' => []])
                </div>

                @foreach ($users as $user)
                    <div x-show="mode === 'edit' && selected === {{ $user->id }}" x-cloak>
                        @include('admin.users._form', ['user' => $user, 'modules' => $modules, 'grantedIds' => $user->permissions->pluck('id')->all()])
                    </div>
                @endforeach

                <div x-show="mode === 'list'" class="flex h-48 items-center justify-center rounded-xl border border-dashed border-gray-300 p-4 text-center text-sm text-gray-400 dark:border-gray-600">
                    Click Edit on a user, or Add User, to get started.
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
