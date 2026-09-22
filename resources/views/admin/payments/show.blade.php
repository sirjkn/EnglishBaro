<x-layouts.admin :title="'Payment '.$payment->transaction_id">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Payment {{ $payment->transaction_id }}</h1>

    <div class="mt-6 grid grid-cols-1 gap-8 lg:grid-cols-2">
        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Student</dt><dd class="text-gray-900 dark:text-white">{{ $payment->user->name }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Track</dt><dd class="text-gray-900 dark:text-white">{{ $payment->track->track_code }} — {{ $payment->track->name }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Amount</dt><dd class="text-gray-900 dark:text-white">{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Method</dt><dd class="text-gray-900 dark:text-white">{{ ucfirst($payment->payment_method) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Status</dt><dd><x-payment-status :status="$payment->status" /></dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Gateway Reference</dt><dd class="text-gray-900 dark:text-white">{{ $payment->gateway_reference ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Verified At</dt><dd class="text-gray-900 dark:text-white">{{ $payment->verified_at?->format('M d, Y H:i') ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Date</dt><dd class="text-gray-900 dark:text-white">{{ $payment->created_at->format('M d, Y H:i') }}</dd></div>
            </dl>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
            <h2 class="font-semibold text-gray-900 dark:text-white">Transaction Timeline</h2>
            @if ($payment->transactions->isEmpty())
                <x-empty-state class="mt-4" message="No transaction events recorded." />
            @else
                <ul class="mt-4 space-y-3 text-sm">
                    @foreach ($payment->transactions as $transaction)
                        <li class="rounded-lg bg-gray-50 p-3 dark:bg-gray-900">
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-900 dark:text-white">{{ ucfirst($transaction->event) }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $transaction->occurred_at->format('M d, Y H:i') }}</span>
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Status: {{ $transaction->status }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</x-layouts.admin>
