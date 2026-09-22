<x-layouts.admin :title="'Payments'">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Payments</h1>

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card label="Total Revenue" :value="'$'.number_format($overview['total_revenue'], 2)" />
        <x-stat-card label="Successful Payments" :value="$overview['successful']" />
        <x-stat-card label="Pending Payments" :value="$overview['pending']" />
    </div>

    <form method="GET" class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-5">
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

    <div class="mt-6 overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
            <thead>
                <tr class="bg-gray-50 text-left text-xs uppercase text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                    <th class="px-4 py-2">Transaction ID</th>
                    <th class="px-4 py-2">Student</th>
                    <th class="px-4 py-2">Course</th>
                    <th class="px-4 py-2">Amount</th>
                    <th class="px-4 py-2">Method</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Date</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-gray-800">
                @forelse ($payments as $payment)
                    <tr>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $payment->transaction_id }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $payment->user->name }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $payment->course->title }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ ucfirst($payment->payment_method) }}</td>
                        <td class="px-4 py-3"><x-payment-status :status="$payment->status" /></td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $payment->created_at->format('M d, Y') }}</td>
                        <td class="px-4 py-3 text-right"><a href="{{ route('admin.payments.show', $payment) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No payments found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $payments->links() }}</div>
</x-layouts.admin>
