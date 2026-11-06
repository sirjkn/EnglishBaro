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
        $enrollments = $user->enrollments()->where('status', 'active')->with('subscription')->get();
        $enrolledTrackIds = $enrollments->pluck('track_id');
        $currentTrack = $user->studentProfile?->track?->load('course');

        $upcomingTrack = ($currentTrack && ! $enrolledTrackIds->contains($currentTrack->id))
            ? $currentTrack
            : null;

        // Same "days remaining on the active subscription" the dashboard shows.
        $nearestExpiry = $enrollments
            ->map(fn ($enrollment) => $enrollment->subscription)
            ->filter(fn ($subscription) => $subscription && $subscription->status === 'active')
            ->sortBy('expires_at')
            ->first();

        $successfulPayments = $user->payments()->where('status', 'successful')->get();
        $totalPaid = $successfulPayments->sum('amount');
        $paidCurrency = $successfulPayments->first()?->currency ?? '';

        return view('student.payments', [
            'payments' => $payments,
            'upcomingTrack' => $upcomingTrack,
            'nearestExpiry' => $nearestExpiry,
            'totalPaid' => $totalPaid,
            'paidCurrency' => $paidCurrency,
            'successfulPaymentsCount' => $successfulPayments->count(),
        ]);
    }
}
