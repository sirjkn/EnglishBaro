<?php

namespace App\Services;

use App\Models\Track;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * One payment unlocks every track in a course, so a student may freely work
 * in any track they're enrolled in — this no longer enforces the course's
 * track order. The real gate is the active enrollment/subscription check
 * in TrackPolicy::learn().
 */
class TrackUnlockService
{
    public function isUnlocked(User $user, Track $track): bool
    {
        return true;
    }

    /**
     * @return Collection<int, int>
     */
    public function unlockedTrackIds(User $user, Collection $tracks): Collection
    {
        return $tracks->filter(fn (Track $track) => $this->isUnlocked($user, $track))->pluck('id')->values();
    }

    public function currentTrackInCourse(User $user, int $courseId): ?Track
    {
        $track = $user->studentProfile?->track;

        return $track && $track->course_id === $courseId ? $track : null;
    }
}
