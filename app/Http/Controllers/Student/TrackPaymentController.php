<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
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

        DB::transaction(function () use ($user, $track, $validated, $region, $amount) {
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

            $enrollment = Enrollment::query()->updateOrCreate(
                ['user_id' => $user->id, 'track_id' => $track->id],
                ['status' => 'active', 'enrolled_at' => now()]
            );

            Subscription::query()->create([
                'user_id' => $user->id,
                'track_id' => $track->id,
                'enrollment_id' => $enrollment->id,
                'payment_id' => $payment->id,
                'starts_at' => now(),
                'expires_at' => now()->addDays($track->subscription_days),
                'duration_days' => $track->subscription_days,
                'status' => 'active',
                'region' => $region,
            ]);
        });

        return redirect()->route('student.dashboard')
            ->with('status', "Payment successful! You now have access to {$track->track_code}.");
    }
}
