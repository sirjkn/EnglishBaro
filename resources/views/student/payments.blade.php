<x-layouts.student :title="'Payments'">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Payments</h1>

    <div class="mt-6">
        <h2 class="font-semibold text-gray-900 dark:text-white">Upcoming Payments</h2>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Tracks are prepaid in full — this shows the next track you're eligible for but haven't purchased yet, not tracks you've already paid for.</p>

        @if (! $upcomingTrack)
            <x-empty-state class="mt-4" message="No upcoming payments." />
        @else
            <div class="mt-4 overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                    <thead>
                        <tr class="bg-indigo-600 text-left text-xs uppercase text-white dark:bg-indigo-800 dark:text-white divide-x divide-white">
                            <th class="px-4 py-2">Track</th>
                            <th class="px-4 py-2">Price</th>
                            <th class="px-4 py-2">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-indigo-100 dark:divide-gray-800 dark:bg-gray-800">
                        <tr class="odd:bg-white even:bg-indigo-50 dark:odd:bg-gray-800 dark:even:bg-gray-900 divide-x divide-gray-200 dark:divide-gray-700">
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $upcomingTrack->track_code }} — {{ $upcomingTrack->name }}</td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $upcomingTrack->currency }} {{ number_format($upcomingTrack->defaultPrice(), 2) }}</td>
                            <td class="px-4 py-3 text-right">
                                <x-payment-method-modal :track="$upcomingTrack" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-8">
        <h2 class="font-semibold text-gray-900 dark:text-white">Payment History</h2>

        @if ($payments->isEmpty())
            <x-empty-state class="mt-4" message="No payments yet." />
        @else
            <div class="mt-4 overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                    <thead>
                        <tr class="bg-indigo-600 text-left text-xs uppercase text-white dark:bg-indigo-800 dark:text-white divide-x divide-white">
                            <th class="px-4 py-2">Transaction ID</th>
                            <th class="px-4 py-2">Track</th>
                            <th class="px-4 py-2">Amount</th>
                            <th class="px-4 py-2">Method</th>
                            <th class="px-4 py-2">Status</th>
                            <th class="px-4 py-2">Date</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-indigo-100 dark:divide-gray-800 dark:bg-gray-800">
                        @foreach ($payments as $payment)
                            <tr class="odd:bg-white even:bg-indigo-50 dark:odd:bg-gray-800 dark:even:bg-gray-900 divide-x divide-gray-200 dark:divide-gray-700">
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $payment->transaction_id }}</td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $payment->track->track_code }} — {{ $payment->track->name }}</td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ ucfirst($payment->payment_method) }}</td>
                                <td class="px-4 py-3"><x-payment-status :status="$payment->status" /></td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $payment->created_at->format('M d, Y') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('student.payments.receipt', $payment) }}" target="_blank" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Receipt</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $payments->links() }}</div>
        @endif
    </div>
</x-layouts.student>
