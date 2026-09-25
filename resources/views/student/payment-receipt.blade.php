<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Receipt {{ $payment->transaction_id }} - {{ config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            @media print {
                .no-print { display: none !important; }
                body { background: white !important; }
            }
        </style>
    </head>
    <body class="bg-gray-50 font-sans text-gray-900 antialiased">
        <div class="mx-auto max-w-2xl px-4 py-10">
            <div class="no-print mb-6 flex items-center justify-between">
                <a href="{{ route('student.payments') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">&larr; Back to Payments</a>
                <button type="button" onclick="window.print()" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                    Print / Save as PDF
                </button>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
                <div class="flex items-start justify-between border-b border-gray-200 pb-6">
                    <div>
                        <img src="{{ asset('images/logo-wordmark.png') }}" alt="{{ $company['name'] }}" class="h-8 w-auto">
                        @if ($company['address'])
                            <p class="mt-2 text-xs text-gray-500">{{ $company['address'] }}</p>
                        @endif
                        @if ($company['email'])
                            <p class="text-xs text-gray-500">{{ $company['email'] }}</p>
                        @endif
                    </div>
                    <div class="text-right">
                        <h1 class="text-lg font-bold text-gray-900">Payment Receipt</h1>
                        <p class="mt-1 text-xs text-gray-500">{{ $payment->transaction_id }}</p>
                        <p class="text-xs text-gray-500">{{ $payment->created_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-6 text-sm">
                    <div>
                        <p class="text-xs font-medium uppercase text-gray-400">Billed To</p>
                        <p class="mt-1 font-medium text-gray-900">{{ $payment->user->name }}</p>
                        <p class="text-gray-600">{{ $payment->user->email }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-medium uppercase text-gray-400">Status</p>
                        <p class="mt-1"><x-payment-status :status="$payment->status" /></p>
                    </div>
                </div>

                <table class="mt-8 w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-xs uppercase text-gray-400">
                            <th class="pb-2">Description</th>
                            <th class="pb-2 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-gray-100">
                            <td class="py-3">
                                <p class="font-medium text-gray-900">{{ $payment->track->track_code }} &middot; {{ $payment->track->name }}</p>
                                <p class="text-xs text-gray-500">{{ $payment->subscription_days }} days access &middot; Paid via {{ ucfirst($payment->payment_method) }}</p>
                            </td>
                            <td class="py-3 text-right font-medium text-gray-900">{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="pt-4 text-right font-semibold text-gray-900">Total</td>
                            <td class="pt-4 text-right text-lg font-bold text-gray-900">{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>

                <p class="mt-8 border-t border-gray-200 pt-4 text-center text-xs text-gray-400">
                    This is a computer-generated receipt for {{ config('app.name') }}.
                </p>
            </div>
        </div>
    </body>
</html>
