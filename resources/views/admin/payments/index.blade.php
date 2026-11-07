<x-layouts.admin :title="'Payments'">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Payments</h1>

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card label="Total Revenue" :value="'$'.number_format($overview['total_revenue'], 2)" icon="currency-dollar" color="emerald" />
        <x-stat-card label="Successful Payments" :value="$overview['successful']" icon="user-check" color="indigo" />
        <x-stat-card label="Pending Payments" :value="$overview['pending']" icon="clock" color="amber" />
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2" x-data="{ open: {{ $openPaymentId ?? 'null' }} }">
        {{-- 50%: filters + payments table --}}
        <div>
            <form method="GET" class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <input type="text" name="transaction_id" value="{{ $filters['transaction_id'] ?? '' }}" placeholder="Transaction ID" class="rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                <select name="method" class="rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    <option value="">All Methods</option>
                    <option value="flutterwave" @selected(($filters['method'] ?? '') === 'flutterwave')>Flutterwave</option>
                    <option value="mpesa" @selected(($filters['method'] ?? '') === 'mpesa')>M-Pesa</option>
                    <option value="paypal" @selected(($filters['method'] ?? '') === 'paypal')>PayPal</option>
                    <option value="waafipay" @selected(($filters['method'] ?? '') === 'waafipay')>WaafiPay</option>
                </select>
                <select name="status" class="rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    <option value="">All Statuses</option>
                    @foreach (['pending','processing','successful','failed','cancelled','refunded'] as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200">Filter</button>
            </form>

            <div class="mt-4 overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 text-xs dark:divide-gray-700">
                    <thead>
                        <tr class="bg-indigo-600 text-left text-xs uppercase text-white dark:bg-indigo-800 dark:text-white divide-x divide-white">
                            <th class="px-3 py-2">Transaction ID</th>
                            <th class="px-3 py-2">Student</th>
                            <th class="px-3 py-2">Amount</th>
                            <th class="px-3 py-2">Status</th>
                            <th class="px-3 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-indigo-100 dark:divide-gray-800 dark:bg-gray-800">
                        @forelse ($payments as $payment)
                            <tr
                                class="odd:bg-white even:bg-indigo-50 dark:odd:bg-gray-800 dark:even:bg-gray-900 divide-x divide-gray-200 dark:divide-gray-700 cursor-pointer"
                                @click="open = {{ $payment->id }}"
                                :class="open === {{ $payment->id }} ? '!bg-indigo-100 dark:!bg-indigo-900/40' : ''"
                            >
                                <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ $payment->transaction_id }}</td>
                                <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ $payment->user->name }}</td>
                                <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</td>
                                <td class="px-3 py-2"><x-payment-status :status="$payment->status" /></td>
                                <td class="px-3 py-2 text-right">
                                    <button type="button" class="text-xs font-medium text-indigo-600 hover:text-indigo-500">View</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No payments found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $payments->links() }}</div>
        </div>

        {{-- 50%: selected payment's details --}}
        <div class="lg:sticky lg:top-4 lg:self-start">
            <div x-show="open === null" class="flex h-48 items-center justify-center rounded-xl border border-dashed border-gray-300 px-4 text-center text-sm text-gray-400 dark:border-gray-600">
                Click a payment on the left to see its details.
            </div>

            @foreach ($payments as $payment)
                <div x-show="open === {{ $payment->id }}" x-cloak>
                    <div class="flex flex-wrap items-center justify-between gap-2 rounded-t-xl bg-indigo-600 px-4 py-3 dark:bg-indigo-800">
                        <h2 class="text-sm font-bold text-white">Payment {{ $payment->transaction_id }}</h2>
                        <button type="button" @click="open = null" class="rounded-full px-2 text-lg leading-none text-white hover:bg-white/10">&times;</button>
                    </div>

                    <div class="grid grid-cols-1 gap-6 rounded-b-xl border border-gray-200 p-4 dark:border-gray-700">
                        <div>
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Student</dt><dd class="text-gray-900 dark:text-white"><a href="{{ route('admin.students.show', $payment->user) }}" class="text-indigo-600 hover:text-indigo-500 hover:underline dark:text-indigo-400">{{ $payment->user->name }}</a></dd></div>
                                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Track</dt><dd class="text-gray-900 dark:text-white">{{ $payment->track->track_code }} — {{ $payment->track->name }}</dd></div>
                                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Amount</dt><dd class="text-gray-900 dark:text-white">{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</dd></div>
                                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Method</dt><dd class="text-gray-900 dark:text-white">{{ ucfirst($payment->payment_method) }}</dd></div>
                                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Status</dt><dd><x-payment-status :status="$payment->status" /></dd></div>
                                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Gateway Reference</dt><dd class="text-gray-900 dark:text-white">{{ $payment->gateway_reference ?? '—' }}</dd></div>
                                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Verified At</dt><dd class="text-gray-900 dark:text-white">{{ $payment->verified_at?->format('M d, Y H:i') ?? '—' }}</dd></div>
                                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Date</dt><dd class="text-gray-900 dark:text-white">{{ $payment->created_at->format('M d, Y H:i') }}</dd></div>
                            </dl>
                        </div>

                        <div class="border-t border-gray-100 pt-4 dark:border-gray-700">
                            <h3 class="font-semibold text-gray-900 dark:text-white">Transaction Timeline</h3>
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
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.admin>
