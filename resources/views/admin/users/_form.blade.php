<form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}"
      x-data="{ userType: '{{ old('user_type', $user->user_type ?? 'staff') }}' }" class="space-y-6">
    @csrf
    @if ($user->exists) @method('PUT') @endif
    <input type="hidden" name="redirect" value="{{ route('admin.users.index', $user->exists ? ['open' => $user->id] : [], false) }}">

    <div class="rounded-xl border border-black/10 bg-white p-6 dark:border-white/10 dark:bg-gray-800">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Password {{ $user->exists ? '(leave blank to keep current)' : '' }}
                </label>
                <input type="password" name="password" autocomplete="new-password" {{ $user->exists ? '' : 'required' }} class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                <select name="user_type" x-model="userType" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    <option value="admin">Admin &mdash; full access</option>
                    <option value="staff">Staff &mdash; customizable access</option>
                </select>
            </div>
        </div>
    </div>

    <div class="mt-6 rounded-xl border border-black/10 bg-white p-6 dark:border-white/10 dark:bg-gray-800" x-show="userType === 'staff'" x-cloak>
        <h2 class="font-semibold text-gray-900 dark:text-white">Module Access</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tick every menu/module this staff account should be able to open. Everything else stays hidden and blocked.</p>

        <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
            @foreach ($modules as $module)
                <label class="flex items-center gap-2 rounded-md border border-gray-200 px-3 py-2 text-sm dark:border-gray-700">
                    <input type="checkbox" name="permissions[]" value="{{ $module->id }}" {{ in_array($module->id, old('permissions', $grantedIds)) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600 dark:border-gray-600">
                    {{ $module->name }}
                </label>
            @endforeach
        </div>
    </div>

    <div x-show="userType === 'admin'" x-cloak class="mt-6 rounded-xl border border-indigo-200 bg-indigo-50 p-4 text-sm text-indigo-700 dark:border-indigo-900 dark:bg-indigo-900/20 dark:text-indigo-300">
        Admin accounts automatically have every module &mdash; nothing to configure.
    </div>

    <div class="mt-6 flex gap-2">
        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
            {{ $user->exists ? 'Save Changes' : 'Create User' }}
        </button>
        <button type="button" @click="mode = 'list'" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">Cancel</button>
    </div>
</form>
