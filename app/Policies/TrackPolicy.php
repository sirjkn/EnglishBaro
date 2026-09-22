<?php

namespace App\Policies;

use App\Models\Track;
use App\Models\User;

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

    public function learn(User $user, Track $track): bool
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

        return $subscription && $subscription->isActive();
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
