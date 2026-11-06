<?php

namespace App\Services;

use App\Models\User;

class StudentDashboardSummaryService
{
    public function __construct(
        private readonly PlacementTestService $placementTest,
        private readonly SessionQuotaService $sessionQuota,
    ) {}

    /**
     * The profile card + "Track Expires / Sessions This Month / Total Paid"
     * stat cards shown at the top of the student dashboard — also reused at
     * the top of Landing for a logged-in student, so it's computed in one
     * place rather than twice.
     *
     * @return array<string, mixed>
     */
    public function forUser(User $user): array
    {
        $needsPlacementTest = $user->isStudent() && ! $this->placementTest->hasCompletedPlacement($user);

        $enrollments = $user->enrollments()
            ->where('status', 'active')
            ->with(['track.thumbnail', 'progress', 'subscription'])
            ->latest('enrolled_at')
            ->get();

        // Tracks are prepaid in full, so an active subscription is never an
        // "upcoming payment" — it's already paid. The only real upcoming
        // payment is the next track the student has been placed/promoted
        // into but hasn't purchased yet.
        $currentTrack = $user->studentProfile?->track?->load('course');
        $enrolledTrackIds = $enrollments->pluck('track_id');

        $upcomingTrack = ($currentTrack && ! $enrolledTrackIds->contains($currentTrack->id))
            ? $currentTrack
            : null;

        $activeSubscription = $enrollments
            ->map(fn ($enrollment) => $enrollment->subscription)
            ->filter()
            ->filter(fn ($subscription) => $subscription->status === 'active')
            ->sortBy('expires_at')
            ->first();

        $nearestExpiry = $enrollments
            ->map(fn ($enrollment) => $enrollment->subscription)
            ->filter(fn ($subscription) => $subscription && $subscription->status === 'active')
            ->sortBy('expires_at')
            ->first();

        $userSessions = $user->userSessions()->get();
        $latestSession = $userSessions->sortByDesc('last_activity_at')->first();
        $sessionsThisMonth = $this->sessionQuota->countThisMonth($user);

        $successfulPayments = $user->payments()->where('status', 'successful')->get();
        $totalPaid = $successfulPayments->sum('amount');
        $paidCurrency = $successfulPayments->first()?->currency ?? '';

        return [
            'enrollments' => $enrollments,
            'upcomingTrack' => $upcomingTrack,
            'activeSubscription' => $activeSubscription,
            'studentProfile' => $user->studentProfile,
            'needsPlacementTest' => $needsPlacementTest,
            'nearestExpiry' => $nearestExpiry,
            'latestSession' => $latestSession,
            'sessionsThisMonth' => $sessionsThisMonth,
            'sessionMonthlyLimit' => SessionQuotaService::MONTHLY_LIMIT,
            'totalPaid' => $totalPaid,
            'paidCurrency' => $paidCurrency,
            'successfulPaymentsCount' => $successfulPayments->count(),
        ];
    }
}
