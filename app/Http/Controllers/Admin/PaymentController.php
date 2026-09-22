<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
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

        $payments = $query->latest()->paginate(15)->withQueryString();

        return view('admin.payments.index', [
            'payments' => $payments,
            'overview' => [
                'total_revenue' => (float) Payment::query()->where('status', 'successful')->sum('amount'),
                'successful' => Payment::query()->where('status', 'successful')->count(),
                'pending' => Payment::query()->whereIn('status', ['pending', 'processing'])->count(),
            ],
            'filters' => $request->only(['student', 'track', 'method', 'status', 'transaction_id']),
        ]);
    }

    public function show(Payment $payment): View
    {
        $this->authorize('view', $payment);

        $payment->load(['user', 'track', 'transactions']);

        return view('admin.payments.show', ['payment' => $payment]);
    }
}
