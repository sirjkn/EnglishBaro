<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Services\PlacementTestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(PlacementTestService $placementTest): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user->isStudent() && ! $placementTest->hasCompletedPlacement($user)) {
            return redirect()->route('student.placement-test');
        }

        $enrollments = $user->enrollments()
            ->where('status', 'active')
            ->with(['track', 'progress', 'subscription'])
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

        return view('student.dashboard', [
            'enrollments' => $enrollments,
            'upcomingTrack' => $upcomingTrack,
            'activeSubscription' => $activeSubscription,
            'recentNotifications' => $recentNotifications,
            'studentProfile' => $user->studentProfile,
        ]);
    }
}
