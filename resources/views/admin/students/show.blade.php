<x-layouts.admin :title="$student->name">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $student->name }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $student->studentProfile?->student_id }} &middot; {{ $student->email }}</p>
        </div>
        <a href="{{ route('admin.students.edit', $student) }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">Edit</a>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-8 lg:grid-cols-2">
        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
            <h2 class="font-semibold text-gray-900 dark:text-white">Personal Information</h2>
            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Phone</dt><dd class="text-gray-900 dark:text-white">{{ $student->studentProfile?->phone ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Country</dt><dd class="text-gray-900 dark:text-white">{{ $student->studentProfile?->country ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Track</dt><dd class="text-gray-900 dark:text-white">{{ $student->studentProfile?->track?->track_code ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Referral Email</dt><dd class="text-gray-900 dark:text-white">{{ $student->studentProfile?->referral_email ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Status</dt><dd><x-badge :color="$student->is_active ? 'green' : 'gray'">{{ $student->is_active ? 'Active' : 'Inactive' }}</x-badge></dd></div>
            </dl>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
            <h2 class="font-semibold text-gray-900 dark:text-white">Tracks</h2>
            @if ($student->enrollments->isEmpty())
                <x-empty-state class="mt-4" message="No enrollments yet." />
            @else
                <div class="mt-4 space-y-3">
                    @foreach ($student->enrollments as $enrollment)
                        <div class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 text-sm dark:bg-gray-900">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $enrollment->track->track_code }} — {{ $enrollment->track->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Enrolled {{ $enrollment->enrolled_at->format('M d, Y') }}
                                    @if ($enrollment->subscription)
                                        &middot; Expires {{ $enrollment->subscription->expires_at->format('M d, Y') }}
                                    @endif
                                </p>
                            </div>
                            <x-badge :color="$enrollment->status === 'active' ? 'green' : 'gray'">{{ ucfirst($enrollment->status) }}</x-badge>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="mt-8 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-gray-900 dark:text-white">Active Sessions</h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Sessions used this month: <span class="font-semibold {{ $sessionsThisMonth >= $sessionMonthlyLimit ? 'text-red-600' : 'text-gray-700 dark:text-gray-300' }}">{{ $sessionsThisMonth }} / {{ $sessionMonthlyLimit }}</span>
                </p>
            </div>
            <form method="POST" action="{{ route('admin.students.reset-session-quota', $student) }}" onsubmit="return confirm('Reset this student\'s session quota for the current month?');">
                @csrf
                <button type="submit" class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">Reset Session Quota</button>
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
                    @foreach ($student->userSessions as $session)
                        <tr class="odd:bg-white even:bg-indigo-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                            <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $session->device }}</td>
                            <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $session->browser }}</td>
                            <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $session->ip_address }}</td>
                            <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $session->last_activity_at?->diffForHumans() }}</td>
                            <td class="py-2 pr-4"><x-badge :color="$session->status === 'active' ? 'green' : 'gray'">{{ ucfirst($session->status) }}</x-badge></td>
                            <td class="py-2 text-right">
                                @if ($session->status === 'active')
                                    <form method="POST" action="{{ route('admin.sessions.destroy', $session) }}" onsubmit="return confirm('Terminate this session?');">
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

    <div class="mt-8 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h2 class="font-semibold text-gray-900 dark:text-white">Payments</h2>
        @if ($student->payments->isEmpty())
            <x-empty-state class="mt-4" message="No payment history yet." />
        @else
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                    <thead>
                        <tr class="bg-indigo-100 text-left text-xs uppercase text-indigo-900 dark:bg-indigo-950 dark:text-indigo-200 divide-x divide-white">
                            <th class="py-2 pr-4">Transaction</th>
                            <th class="py-2 pr-4">Track</th>
                            <th class="py-2 pr-4">Amount</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-indigo-100 dark:divide-gray-800">
                        @foreach ($student->payments as $payment)
                            <tr class="odd:bg-white even:bg-indigo-50 dark:odd:bg-gray-800 dark:even:bg-gray-900">
                                <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $payment->transaction_id }}</td>
                                <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $payment->track->track_code }}</td>
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
</x-layouts.admin>
