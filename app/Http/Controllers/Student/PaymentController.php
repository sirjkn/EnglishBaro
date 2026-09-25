<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function receipt(Payment $payment): View
    {
        abort_unless($payment->user_id === Auth::id(), 404);

        return view('student.payment-receipt', [
            'payment' => $payment->load('track', 'user'),
            'company' => [
                'name' => CompanySetting::get('company_name', config('app.name')),
                'address' => CompanySetting::get('address'),
                'email' => CompanySetting::get('support_email') ?? CompanySetting::get('email'),
            ],
        ]);
    }

    public function __invoke(): View
    {
        $user = Auth::user();

        $payments = $user->payments()->with('track')->latest()->paginate(10);

        // Tracks are prepaid in full, so an active subscription is never
        // "upcoming" — it's already paid (it's in Payment History below).
        // The only real upcoming payment is the next track the student has
        // been promoted/placed into but hasn't purchased yet.
        $enrolledTrackIds = $user->enrollments()->where('status', 'active')->pluck('track_id');
        $currentTrack = $user->studentProfile?->track;

        $upcomingTrack = ($currentTrack && ! $enrolledTrackIds->contains($currentTrack->id))
            ? $currentTrack
            : null;

        return view('student.payments', [
            'payments' => $payments,
            'upcomingTrack' => $upcomingTrack,
        ]);
    }
}
