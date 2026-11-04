<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Services\PlacementTestService;
use App\Services\SessionQuotaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(PlacementTestService $placementTest, SessionQuotaService $sessionQuota): View
    {
        $user = Auth::user();

        // Placement is no longer a hard gate before the dashboard — it's
        // shown as a to-do so a brand-new student can still see around,
        // but nothing is payable/enrollable until it's done (TrackPolicy
        // denies enrollment without a placed track).
        $needsPlacementTest = $user->isStudent() && ! $placementTest->hasCompletedPlacement($user);

        $enrollments = $user->enrollments()
            ->where('status', 'active')
            ->with(['track.thumbnail', 'progress', 'subscription'])
            ->latest('enrolled_at')
            ->get();

        // Tracks are prepaid in full, so an active subscription is never an
        // "upcoming payment" — it's already paid (see Payment History). The
        // only real upcoming payment is the next track the student has been
        // promoted into (or placed into) but hasn't purchased yet.
        $currentTrack = $user->studentProfile?->track;
        $enrolledTrackIds = $enrollments->pluck('track_id');

        $upcomingTrack = ($currentTrack && ! $enrolledTrackIds->contains($currentTrack->id))
            ? $currentTrack
            : null;

        // For the countdown badge: days left on the subscription that's actually active right now.
        $activeSubscription = $enrollments
            ->map(fn ($enrollment) => $enrollment->subscription)
            ->filter()
            ->filter(fn ($subscription) => $subscription->status === 'active')
            ->sortBy('expires_at')
            ->first();

        $recentNotifications = AppNotification::query()
            ->where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        // Same "Track Expires / Sessions This Month / Total Paid" cards the
        // admin sees on this student's profile, shown here to the student.
        $nearestExpiry = $enrollments
            ->map(fn ($enrollment) => $enrollment->subscription)
            ->filter(fn ($subscription) => $subscription && $subscription->status === 'active')
            ->sortBy('expires_at')
            ->first();

        $userSessions = $user->userSessions()->get();
        $latestSession = $userSessions->sortByDesc('last_activity_at')->first();
        $sessionsThisMonth = $sessionQuota->countThisMonth($user);

        $successfulPayments = $user->payments()->where('status', 'successful')->get();
        $totalPaid = $successfulPayments->sum('amount');
        $paidCurrency = $successfulPayments->first()?->currency ?? '';

        return view('student.dashboard', [
            'enrollments' => $enrollments,
            'upcomingTrack' => $upcomingTrack,
            'activeSubscription' => $activeSubscription,
            'recentNotifications' => $recentNotifications,
            'studentProfile' => $user->studentProfile,
            'needsPlacementTest' => $needsPlacementTest,
            'nearestExpiry' => $nearestExpiry,
            'latestSession' => $latestSession,
            'sessionsThisMonth' => $sessionsThisMonth,
            'sessionMonthlyLimit' => SessionQuotaService::MONTHLY_LIMIT,
            'totalPaid' => $totalPaid,
            'paidCurrency' => $paidCurrency,
            'successfulPaymentsCount' => $successfulPayments->count(),
        ]);
    }
}
