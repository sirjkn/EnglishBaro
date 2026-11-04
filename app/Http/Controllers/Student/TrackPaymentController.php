<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Track;
use App\Services\RegionPricingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TrackPaymentController extends Controller
{
    private const GATEWAYS = ['flutterwave', 'mpesa', 'paypal', 'waafipay'];

    /**
     * Demo checkout: no live gateway is wired up, so this records the payment
     * as successful immediately and activates the enrollment, the same state
     * a real gateway's webhook would eventually produce.
     */
    public function pay(Request $request, Track $track, RegionPricingService $regionPricingService): RedirectResponse
    {
        $this->authorize('enroll', $track);

        $validated = $request->validate([
            'gateway' => ['required', 'in:'.implode(',', self::GATEWAYS)],
        ]);

        $user = Auth::user();
        $region = $regionPricingService->resolveRegion($request);
        $amount = $track->priceForRegion($region);

        // A single payment unlocks every published track, not just the one
        // the student clicked "Pay" on — the catalogue is one progression
        // (A1 through the highest track), and students simply climb through
        // it level by level rather than buying each step separately.
        $allTracks = Track::query()->where('status', 'published')->get();

        DB::transaction(function () use ($user, $track, $allTracks, $validated, $region, $amount) {
            $payment = Payment::query()->create([
                'transaction_id' => 'EB-'.Str::upper(Str::random(12)),
                'user_id' => $user->id,
                'track_id' => $track->id,
                'amount' => $amount,
                'currency' => $track->currency,
                'region' => $region,
                'payment_method' => $validated['gateway'],
                'status' => 'successful',
                'subscription_days' => $track->subscription_days,
                'verified_at' => now(),
            ]);

            $expiresAt = now()->addDays($track->subscription_days);

            foreach ($allTracks as $eachTrack) {
                $enrollment = Enrollment::query()->updateOrCreate(
                    ['user_id' => $user->id, 'track_id' => $eachTrack->id],
                    ['status' => 'active', 'enrolled_at' => now()]
                );

                Subscription::query()->updateOrCreate(
                    ['user_id' => $user->id, 'track_id' => $eachTrack->id, 'enrollment_id' => $enrollment->id],
                    [
                        'payment_id' => $payment->id,
                        'starts_at' => now(),
                        'expires_at' => $expiresAt,
                        'duration_days' => $track->subscription_days,
                        'status' => 'active',
                        'region' => $region,
                    ]
                );
            }

            AppNotification::create([
                'user_id' => $user->id,
                'type' => 'payment_successful',
                'title' => 'Payment successful',
                'body' => "You now have full access to every course, starting with {$track->track_code} — {$track->name}.",
                'data' => ['url' => route('student.payments')],
            ]);
        });

        return redirect()->route('student.dashboard')
            ->with('status', "Payment successful! You now have access to every course, starting with {$track->track_code}.");
    }
}
