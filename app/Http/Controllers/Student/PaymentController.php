<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __invoke(): View
    {
        $user = Auth::user();

        $payments = $user->payments()->with('course')->latest()->paginate(10);

        $upcomingSubscriptions = $user->subscriptions()
            ->where('status', 'active')
            ->with('course')
            ->orderBy('expires_at')
            ->get();

        return view('student.payments', [
            'payments' => $payments,
            'upcomingSubscriptions' => $upcomingSubscriptions,
        ]);
    }
}
