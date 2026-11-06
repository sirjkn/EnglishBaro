<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Course $course): bool
    {
        return true;
    }

    /**
     * A student may buy a course if they've taken the placement test (so we
     * know which unit to start them on) and don't already have an active
     * subscription for it. Mirrors TrackPolicy::enroll, checked against the
     * course's units as a whole rather than one track at a time.
     */
    public function enroll(User $user, Course $course): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $placedTrack = $user->studentProfile?->track;

        if (! $placedTrack || $placedTrack->course_id !== $course->id) {
            return false;
        }

        $defaultTrack = $course->defaultTrack();

        if (! $defaultTrack) {
            return false;
        }

        return $user->can('enroll', $defaultTrack);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Course $course): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Course $course): bool
    {
        return $user->isAdmin();
    }
}
