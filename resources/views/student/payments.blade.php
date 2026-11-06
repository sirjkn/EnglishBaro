<x-layouts.student :title="'Payments'">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Payments</h1>

    <div class="mt-6 grid grid-cols-1 gap-2 sm:grid-cols-2 sm:gap-4">
        <x-stat-card
            label="Track Expires"
            :value="$nearestExpiry ? $nearestExpiry->expires_at->format('M j, Y') : 'N/A'"
            :sub="$nearestExpiry ? max(0, (int) floor(now()->diffInDays($nearestExpiry->expires_at, false))).' Days Left' : 'No active subscription'"
            icon="clock"
            color="indigo"
            label-size="text-[10px] sm:text-sm"
        />
        <x-stat-card
            label="Total Paid"
            :value="$paidCurrency.' '.number_format((float) $totalPaid, 2)"
            :sub="$successfulPaymentsCount.' successful payment'.($successfulPaymentsCount === 1 ? '' : 's')"
            icon="credit-card"
            color="emerald"
            label-size="text-[10px] sm:text-sm"
        />
    </div>

    <div class="mt-6">
        <h2 class="font-semibold text-gray-900 dark:text-white">Upcoming Payments</h2>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Courses are prepaid in full — this shows the course you're placed into but haven't purchased yet, not courses you've already paid for.</p>

        @if (! $upcomingTrack)
            <x-empty-state class="mt-4" message="No upcoming payments." />
        @else
            <div class="mt-4 overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 text-xs dark:divide-gray-700">
                    <thead>
                        <tr class="bg-indigo-600 text-left text-xs uppercase text-white dark:bg-indigo-800 dark:text-white divide-x divide-white">
                            <th class="px-4 py-2">Course</th>
                            <th class="px-4 py-2">Price</th>
                            <th class="px-4 py-2">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-indigo-100 dark:divide-gray-800 dark:bg-gray-800">
                        <tr class="odd:bg-white even:bg-indigo-50 dark:odd:bg-gray-800 dark:even:bg-gray-900 divide-x divide-gray-200 dark:divide-gray-700">
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $upcomingTrack->course->name }} (starting {{ $upcomingTrack->track_code }})</td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $upcomingTrack->course->currency }} {{ number_format($upcomingTrack->course->defaultPrice(), 2) }}</td>
                            <td class="px-4 py-3 text-right">
                                <x-payment-method-modal :course="$upcomingTrack->course" />
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
            <div x-data="{ receiptUrl: '{{ route('student.payments.receipt', $payments->first()) }}', receiptId: {{ $payments->first()->id }} }" class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2 lg:items-start">
                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                    <table class="min-w-full divide-y divide-gray-200 text-xs dark:divide-gray-700">
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
                                <tr
                                    @click="receiptUrl = '{{ route('student.payments.receipt', $payment) }}'; receiptId = {{ $payment->id }}"
                                    :class="receiptId === {{ $payment->id }} ? 'bg-indigo-50 dark:bg-indigo-900/30' : 'odd:bg-white even:bg-indigo-50/40 dark:odd:bg-gray-800 dark:even:bg-gray-900'"
                                    class="cursor-pointer divide-x divide-gray-200 dark:divide-gray-700"
                                >
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $payment->transaction_id }}</td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $payment->track->track_code }} — {{ $payment->track->name }}</td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ ucfirst($payment->payment_method) }}</td>
                                    <td class="px-4 py-3"><x-payment-status :status="$payment->status" /></td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $payment->created_at->format('M d, Y') }}</td>
                                    <td class="px-4 py-3 text-right">
                                        {{-- Below lg the receipt panel isn't shown, so Open navigates to the full receipt page instead. --}}
                                        <a href="{{ route('student.payments.receipt', $payment) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 lg:hidden">Open</a>
                                        <button
                                            type="button"
                                            @click.stop="receiptUrl = '{{ route('student.payments.receipt', $payment) }}'; receiptId = {{ $payment->id }}"
                                            class="hidden text-sm font-medium lg:inline"
                                            :class="receiptId === {{ $payment->id }} ? 'text-gray-400 cursor-default' : 'text-indigo-600 hover:text-indigo-500'"
                                            :disabled="receiptId === {{ $payment->id }}"
                                        >
                                            <span x-text="receiptId === {{ $payment->id }} ? 'Showing' : 'Open'"></span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="px-4 py-3">{{ $payments->links() }}</div>
                </div>

                <div class="sticky top-20 hidden overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800 lg:block">
                    <iframe :src="receiptUrl" class="h-[80vh] w-full" title="Receipt preview"></iframe>
                </div>
            </div>
        @endif
    </div>
</x-layouts.student>
