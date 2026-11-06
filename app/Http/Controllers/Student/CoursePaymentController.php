<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\RegionPricingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CoursePaymentController extends Controller
{
    private const GATEWAYS = ['flutterwave', 'mpesa', 'paypal', 'waafipay'];

    /**
     * Demo checkout: no live gateway is wired up, so this records the payment
     * as successful immediately and activates every unit (track) in the
     * course, the same state a real gateway's webhook would eventually
     * produce. One payment, one course, every unit inside it unlocked.
     */
    public function pay(Request $request, Course $course, RegionPricingService $regionPricingService): RedirectResponse
    {
        $this->authorize('enroll', $course);

        $validated = $request->validate([
            'gateway' => ['required', 'in:'.implode(',', self::GATEWAYS)],
        ]);

        $user = Auth::user();
        $region = $regionPricingService->resolveRegion($request);
        $amount = $course->priceForRegion($region);

        $tracks = $course->tracks()->where('status', 'published')->get();
        $entryTrack = $course->defaultTrack() ?? $tracks->first();

        DB::transaction(function () use ($user, $course, $tracks, $entryTrack, $validated, $region, $amount) {
            $payment = Payment::query()->create([
                'transaction_id' => 'EB-'.Str::upper(Str::random(12)),
                'user_id' => $user->id,
                'track_id' => $entryTrack?->id,
                'course_id' => $course->id,
                'amount' => $amount,
                'currency' => $course->currency,
                'region' => $region,
                'payment_method' => $validated['gateway'],
                'status' => 'successful',
                'subscription_days' => $course->subscription_days,
                'verified_at' => now(),
            ]);

            $expiresAt = now()->addDays($course->subscription_days);

            foreach ($tracks as $track) {
                $enrollment = Enrollment::query()->updateOrCreate(
                    ['user_id' => $user->id, 'track_id' => $track->id],
                    ['status' => 'active', 'enrolled_at' => now()]
                );

                Subscription::query()->updateOrCreate(
                    ['user_id' => $user->id, 'track_id' => $track->id, 'enrollment_id' => $enrollment->id],
                    [
                        'payment_id' => $payment->id,
                        'starts_at' => now(),
                        'expires_at' => $expiresAt,
                        'duration_days' => $course->subscription_days,
                        'status' => 'active',
                        'region' => $region,
                    ]
                );
            }

            AppNotification::create([
                'user_id' => $user->id,
                'type' => 'payment_successful',
                'title' => 'Payment successful',
                'body' => "You now have full access to every unit in {$course->name}.",
                'data' => ['url' => route('student.payments')],
            ]);
        });

        return redirect()->route('home')
            ->with('status', "Payment successful! You now have access to every unit in {$course->name}.");
    }
}
