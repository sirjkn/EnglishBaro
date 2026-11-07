<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Payment::class);

        $query = Payment::query()->with(['user', 'track']);

        if ($studentId = $request->integer('student')) {
            $query->where('user_id', $studentId);
        }

        if ($trackId = $request->integer('track')) {
            $query->where('track_id', $trackId);
        }

        if ($method = $request->string('method')->value()) {
            $query->where('payment_method', $method);
        }

        if ($status = $request->string('status')->value()) {
            $query->where('status', $status);
        }

        if ($transactionId = $request->string('transaction_id')->trim()->value()) {
            $query->where('transaction_id', 'like', "%{$transactionId}%");
        }

        // Each payment's detail panel is rendered inline on this page (in a
        // hidden block each, toggled by Alpine) rather than on a separate
        // page per payment, so its transactions need loading up front here.
        $payments = $query->with('transactions')->latest()->paginate(15)->withQueryString();

        $openPaymentId = $request->integer('open') ?: null;

        return view('admin.payments.index', [
            'payments' => $payments,
            'openPaymentId' => $openPaymentId,
            'overview' => [
                'total_revenue' => (float) Payment::query()->where('status', 'successful')->sum('amount'),
                'successful' => Payment::query()->where('status', 'successful')->count(),
                'pending' => Payment::query()->whereIn('status', ['pending', 'processing'])->count(),
            ],
            'filters' => $request->only(['student', 'track', 'method', 'status', 'transaction_id']),
        ]);
    }

    public function show(Payment $payment): RedirectResponse
    {
        $this->authorize('view', $payment);

        // The standalone payment page was retired in favor of the inline
        // detail panel on the payments list.
        return redirect()->route('admin.payments.index', ['open' => $payment->id]);
    }
}
