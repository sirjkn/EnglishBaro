<?php

namespace App\Services;

use App\Models\Track;
use App\Models\TrackProgress;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Tracks within a course are taken strictly in order. A student's profile
 * track (set by the placement test, advanced automatically when they finish
 * a track, or changed by an admin) is the only track they can work in;
 * tracks they've already finished stay open for review, and every other
 * track is locked — including easier ones the placement test skipped them
 * past, and harder ones they haven't reached yet.
 */
class TrackUnlockService
{
    public function isUnlocked(User $user, Track $track): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $current = $this->currentTrackInCourse($user, $track->course_id);

        // No placement within this course yet, so there's nothing to
        // sequence against — enrollment/subscription checks still apply.
        if (! $current) {
            return true;
        }

        return $track->id === $current->id || $this->hasCompleted($user, $track);
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

    private function hasCompleted(User $user, Track $track): bool
    {
        return TrackProgress::query()
            ->where('user_id', $user->id)
            ->where('track_id', $track->id)
            ->whereNotNull('completed_at')
            ->exists();
    }
}
