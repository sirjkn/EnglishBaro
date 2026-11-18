<div x-data="{ open: {{ $errors->hasAny(['current_password', 'password']) ? 'true' : 'false' }}, show: false }" {{ $attributes }}>
    <button type="button" @click="open = true" class="flex w-full items-center justify-center gap-1.5 rounded-md border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-50 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-300 dark:hover:bg-gray-700">
        <x-icons.pencil class="h-3 w-3" /> Password
    </button>

    <template x-teleport="body">
        <div x-show="open" x-cloak @keydown.escape.window="open = false" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/50 p-4 sm:items-center" @click.self="open = false">
            <div x-show="open" x-transition class="relative my-8 w-full max-w-md rounded-xl border-2 border-indigo-300 bg-white p-6 shadow-xl dark:border-indigo-700 dark:bg-gray-800" role="dialog" aria-modal="true" aria-labelledby="change-password-title">
                <div class="flex items-start justify-between gap-4">
                    <h2 id="change-password-title" class="text-lg font-bold text-gray-900 dark:text-white">Change Password</h2>
                    <button type="button" @click="open = false" aria-label="Close" class="rounded-md p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('student.account.password') }}" class="mt-5 space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="current_password_modal" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Current Password</label>
                        <input id="current_password_modal" :type="show ? 'text' : 'password'" type="password" name="current_password" required autocomplete="current-password" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                        @error('current_password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="new_password_modal" class="block text-sm font-medium text-gray-700 dark:text-gray-300">New Password</label>
                        <input id="new_password_modal" :type="show ? 'text' : 'password'" type="password" name="password" required autocomplete="new-password" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                        @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="confirm_password_modal" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm New Password</label>
                        <input id="confirm_password_modal" :type="show ? 'text' : 'password'" type="password" name="password_confirmation" required autocomplete="new-password" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    </div>

                    <label class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                        <input type="checkbox" x-model="show" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900">
                        Show passwords
                    </label>

                    <div class="flex items-center justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-700">
                        <button type="button" @click="open = false" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">Cancel</button>
                        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
