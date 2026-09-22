<x-layouts.student :title="'Account'">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Account</h1>

    <div class="mt-6 grid grid-cols-1 gap-8 lg:grid-cols-2">
        {{-- Profile --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
            <h2 class="font-semibold text-gray-900 dark:text-white">Profile</h2>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Student ID: {{ $studentProfile?->student_id }}</p>

            <form method="POST" action="{{ route('student.account.update') }}" class="mt-4 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full Names</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input type="email" value="{{ $user->email }}" disabled class="mt-1 w-full rounded-md border-gray-200 bg-gray-100 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $studentProfile?->phone) }}" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Country</label>
                    <select name="country" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                        <option value="">Select Country</option>
                        @foreach ($countries as $countryOption)
                            <option value="{{ $countryOption }}" @selected(old('country', $studentProfile?->country) === $countryOption)>{{ $countryOption }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Referral Email</label>
                    <input type="email" name="referral_email" value="{{ old('referral_email', $studentProfile?->referral_email) }}" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Current Level</label>
                    <input type="text" value="{{ $studentProfile?->level?->name ?? 'Not set' }}" disabled class="mt-1 w-full rounded-md border-gray-200 bg-gray-100 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
                </div>

                <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save Changes</button>
            </form>
        </div>

        {{-- Password --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
            <h2 class="font-semibold text-gray-900 dark:text-white">Change Password</h2>

            <form method="POST" action="{{ route('student.account.password') }}" class="mt-4 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Current Password</label>
                    <input type="password" name="current_password" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    @error('current_password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">New Password</label>
                    <input type="password" name="password" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                </div>

                <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Update Password</button>
            </form>
        </div>
    </div>

    {{-- Sessions --}}
    <div class="mt-8 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-gray-900 dark:text-white">Sessions</h2>
            <form method="POST" action="{{ route('student.sessions.destroy-others') }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-500">Logout other devices</button>
            </form>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                <thead>
                    <tr class="bg-indigo-100 text-left text-xs uppercase text-indigo-900 dark:bg-indigo-950 dark:text-indigo-200 divide-x divide-white">
                        <th class="py-2 pr-4">Device</th>
                        <th class="py-2 pr-4">Browser</th>
                        <th class="py-2 pr-4">IP</th>
                        <th class="py-2 pr-4">Last Activity</th>
                        <th class="py-2 pr-4">Status</th>
                        <th class="py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-indigo-100 dark:divide-gray-800">
                    @foreach ($sessions as $session)
                        <tr class="odd:bg-white even:bg-indigo-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                            <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $session->device }}</td>
                            <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $session->browser }} &middot; {{ $session->platform }}</td>
                            <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $session->ip_address }}</td>
                            <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $session->last_activity_at?->diffForHumans() }}</td>
                            <td class="py-2 pr-4">
                                <x-badge :color="$session->status === 'active' ? 'green' : 'gray'">{{ ucfirst($session->status) }}</x-badge>
                            </td>
                            <td class="py-2 text-right">
                                @if ($session->status === 'active')
                                    <form method="POST" action="{{ route('student.sessions.destroy', $session) }}" onsubmit="return confirm('Terminate this session?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-500">Terminate</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent Payments --}}
    <div class="mt-8 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h2 class="font-semibold text-gray-900 dark:text-white">Recent Payments</h2>

        @if ($payments->isEmpty())
            <x-empty-state class="mt-4" message="No payment history yet." />
        @else
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                    <thead>
                        <tr class="bg-indigo-100 text-left text-xs uppercase text-indigo-900 dark:bg-indigo-950 dark:text-indigo-200 divide-x divide-white">
                            <th class="py-2 pr-4">Transaction</th>
                            <th class="py-2 pr-4">Amount</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-indigo-100 dark:divide-gray-800">
                        @foreach ($payments as $payment)
                            <tr class="odd:bg-white even:bg-indigo-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                                <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $payment->transaction_id }}</td>
                                <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</td>
                                <td class="py-2 pr-4"><x-payment-status :status="$payment->status" /></td>
                                <td class="py-2 text-gray-700 dark:text-gray-300">{{ $payment->created_at->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-layouts.student>
