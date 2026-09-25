<?php

namespace App\Policies;

use App\Models\Track;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TrackPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Track $track): bool
    {
        return true;
    }

    /**
     * A student may enroll in their current track (from placement, or the
     * highest track they've since finished), or any easier track below it.
     * Reaching a higher track requires finishing the current one first
     * (TrackProgressService promotes them automatically at 100%), or an
     * admin can move them manually at any time.
     */
    public function enroll(User $user, Track $track): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $placedTrack = $user->studentProfile?->track;

        if (! $placedTrack) {
            return false;
        }

        return $track->order <= $placedTrack->order;
    }

    /**
     * A subscription is locked to the pricing region it was purchased under.
     * A student who paid the Africa price cannot use that subscription to
     * learn from North America (or any other region) — they must purchase
     * again from that region. $currentRegion is the student's region right
     * now (resolved from their IP); omit it only where region isn't known.
     */
    public function learn(User $user, Track $track, ?string $currentRegion = null): bool|Response
    {
        if ($user->isAdmin()) {
            return true;
        }

        $enrollment = $track->enrollments()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (! $enrollment) {
            return false;
        }

        $subscription = $enrollment->subscription;

        if (! $subscription || ! $subscription->isActive()) {
            return false;
        }

        if ($subscription->region && $currentRegion && $subscription->region !== $currentRegion) {
            return Response::deny(
                "This subscription was purchased for the {$subscription->region} region. ".
                "To learn from {$currentRegion}, please purchase this track again at the {$currentRegion} price."
            );
        }

        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Track $track): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Track $track): bool
    {
        return $user->isAdmin();
    }
}
