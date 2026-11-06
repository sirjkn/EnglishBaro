<x-layouts.admin :title="$student->name">
    @php
        $initials = collect(explode(' ', trim($student->name)))
            ->filter()
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->take(2)
            ->implode('');

        $avatarPalettes = [
            'bg-indigo-500', 'bg-emerald-500', 'bg-amber-500', 'bg-rose-500', 'bg-sky-500', 'bg-purple-500',
        ];
        $avatarColor = $avatarPalettes[crc32($student->email) % count($avatarPalettes)];

        $nearestExpiry = $student->enrollments
            ->map(fn ($enrollment) => $enrollment->subscription)
            ->filter(fn ($subscription) => $subscription && $subscription->status === 'active')
            ->sortBy('expires_at')
            ->first();

        $latestSession = $student->userSessions->sortByDesc('last_activity_at')->first();

        $successfulPayments = $student->payments->where('status', 'successful');
        $totalPaid = $successfulPayments->sum('amount');
        $paidCurrency = $successfulPayments->first()?->currency ?? '';
    @endphp

    <div class="relative overflow-hidden rounded-xl border border-blue-100 bg-gradient-to-br from-blue-50 to-indigo-100 p-6 dark:border-gray-700 dark:from-gray-800 dark:to-gray-800">
        <div class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-blue-200/50 dark:bg-blue-900/20"></div>

        <div class="relative flex flex-col items-start gap-4 pr-20 sm:flex-row sm:items-center sm:justify-between sm:pr-0">
            <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center">
                <x-student-avatar :profile="$student->studentProfile" size="h-20 w-20" class="border-4 border-white shadow-sm dark:border-gray-700" />
                <div>
                    <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $student->name }}</h1>
                    <p class="mt-0.5 text-sm font-semibold text-blue-700 dark:text-blue-300">{{ $student->studentProfile?->student_id ?? '—' }}</p>
                    <p class="mt-1 flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
                        <x-icons.envelope class="h-4 w-4" /> {{ $student->email }}
                    </p>
                    <div class="mt-1.5">
                        <x-badge :color="$student->is_active ? 'green' : 'gray'" dot>{{ $student->is_active ? 'Active' : 'Inactive' }}</x-badge>
                    </div>
                </div>
            </div>
            <a href="{{ route('admin.students.edit', ['student' => $student, 'redirect' => request()->getRequestUri()]) }}" class="absolute right-0 top-0 flex shrink-0 items-center gap-1.5 rounded-md border border-blue-300 bg-white px-4 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-50 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-300 dark:hover:bg-gray-700 sm:static">
                <x-icons.pencil class="h-4 w-4" /> Edit
            </a>
        </div>

        <div class="relative mt-5 flex flex-wrap items-center gap-2 border-t border-blue-200/60 pt-4 dark:border-gray-700">
            @if ($student->studentProfile?->track)
                <x-badge color="indigo">Current Track: {{ $student->studentProfile->track->track_code }}</x-badge>
            @endif
            <span class="text-xs text-gray-400">&middot;</span>
            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $student->enrollments->count() }} Enrollment{{ $student->enrollments->count() === 1 ? '' : 's' }}</span>
            <span class="text-xs text-gray-400">&middot;</span>
            <span class="text-xs text-gray-500 dark:text-gray-400">Member since {{ $student->created_at->format('M Y') }}</span>
        </div>
    </div>

    @if (session('status'))
        <div class="mt-4 rounded-md bg-green-50 p-3 text-sm text-green-700 dark:bg-green-900/30 dark:text-green-300">
            {{ session('status') }}
        </div>
    @endif

    @if (session('newPassword'))
        <div class="mt-4 rounded-md border border-amber-200 bg-amber-50 p-4 dark:border-amber-900 dark:bg-amber-950/30">
            <p class="text-sm font-semibold text-amber-900 dark:text-amber-200">New password generated — share this with {{ $student->name }} now, it won't be shown again:</p>
            <p class="mt-1 select-all rounded bg-white px-3 py-2 font-mono text-sm text-gray-900 dark:bg-gray-900 dark:text-white">{{ session('newPassword') }}</p>
        </div>
    @endif

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card
            label="Track Expires"
            :value="$nearestExpiry ? $nearestExpiry->expires_at->format('M j, Y') : 'N/A'"
            :sub="$nearestExpiry ? max(0, (int) floor(now()->diffInDays($nearestExpiry->expires_at, false))).' Days Left' : 'No active subscription'"
        />
        <x-stat-card
            label="Sessions This Month"
            :value="$sessionsThisMonth.' of '.$sessionMonthlyLimit"
            :sub="$latestSession ? $latestSession->device.', '.($latestSession->status === 'active' ? 'active now' : $latestSession->last_activity_at?->diffForHumans()) : 'No sessions yet'"
        />
        <x-stat-card
            label="Total Paid"
            :value="$paidCurrency.' '.number_format((float) $totalPaid, 2)"
            :sub="$successfulPayments->count().' successful payment'.($successfulPayments->count() === 1 ? '' : 's')"
        />
    </div>

    <div class="mt-6 grid grid-cols-1 gap-8">
        <div class="rounded-xl border border-black/10 bg-white p-6 dark:border-white/10 dark:bg-gray-800">
            <h2 class="flex items-center gap-2 font-bold text-gray-900 dark:text-white">
                <span class="flex h-7 w-7 items-center justify-center rounded-md bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300">
                    <x-icons.book-open class="h-4 w-4" />
                </span>
                Tracks
            </h2>
            @if ($student->enrollments->isEmpty())
                <x-empty-state class="mt-4" message="No enrollments yet." />
            @else
                <div class="mt-4 space-y-3">
                    @foreach ($student->enrollments as $enrollment)
                        @php
                            $cancelMessage = "This revokes {$student->name}'s access to {$enrollment->track->track_code}. Their payment record is kept — this does not refund them.";
                        @endphp
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
                            <div class="flex items-center gap-3">
                                <x-badge :color="$enrollment->status === 'active' ? 'green' : 'gray'" dot>{{ ucfirst($enrollment->status) }}</x-badge>
                                @if ($enrollment->status === 'active')
                                    <x-double-confirm-form
                                        :action="route('admin.enrollments.cancel', $enrollment)"
                                        label="Cancel"
                                        title="Cancel this enrollment?"
                                        :message="$cancelMessage"
                                        class="text-xs font-medium text-red-600 hover:text-red-500"
                                    />
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="mt-8 rounded-xl border border-black/10 bg-white p-6 dark:border-white/10 dark:bg-gray-800">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="flex items-center gap-2 font-bold text-gray-900 dark:text-white">
                    <span class="flex h-7 w-7 items-center justify-center rounded-md bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300">
                        <x-icons.login class="h-4 w-4" />
                    </span>
                    Active Sessions
                </h2>
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
            <table class="min-w-full divide-y divide-gray-200 text-xs dark:divide-gray-700">
                <thead>
                    <tr class="bg-indigo-600 text-left text-xs uppercase text-white dark:bg-indigo-800 dark:text-white divide-x divide-white">
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
                        <tr class="odd:bg-white even:bg-indigo-50 dark:odd:bg-gray-800 dark:even:bg-gray-900 divide-x divide-gray-200 dark:divide-gray-700">
                            <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $session->device }}</td>
                            <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $session->browser }}</td>
                            <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $session->ip_address }}</td>
                            <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $session->last_activity_at?->diffForHumans() }}</td>
                            <td class="py-2 pr-4"><x-badge :color="$session->status === 'active' ? 'green' : 'gray'" dot>{{ ucfirst($session->status) }}</x-badge></td>
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

    <div class="mt-8 rounded-xl border border-black/10 bg-white p-6 dark:border-white/10 dark:bg-gray-800">
        <h2 class="flex items-center gap-2 font-bold text-gray-900 dark:text-white">
            <span class="flex h-7 w-7 items-center justify-center rounded-md bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300">
                <x-icons.credit-card class="h-4 w-4" />
            </span>
            Payments
        </h2>
        @if ($student->payments->isEmpty())
            <x-empty-state class="mt-4" message="No payment history yet." />
        @else
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-xs dark:divide-gray-700">
                    <thead>
                        <tr class="bg-indigo-600 text-left text-xs uppercase text-white dark:bg-indigo-800 dark:text-white divide-x divide-white">
                            <th class="py-2 pr-4">Transaction</th>
                            <th class="py-2 pr-4">Track</th>
                            <th class="py-2 pr-4">Amount</th>
                            <th class="py-2 pr-4">Method</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-indigo-100 dark:divide-gray-800">
                        @foreach ($student->payments as $payment)
                            <tr class="odd:bg-white even:bg-indigo-50 dark:odd:bg-gray-800 dark:even:bg-gray-900 divide-x divide-gray-200 dark:divide-gray-700">
                                <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $payment->transaction_id }}</td>
                                <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $payment->track->track_code }}</td>
                                <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</td>
                                <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $payment->payment_method ? ucfirst(str_replace('_', ' ', $payment->payment_method)) : '—' }}</td>
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
