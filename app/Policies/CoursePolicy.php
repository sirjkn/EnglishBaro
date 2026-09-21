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

    public function learn(User $user, Course $course): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $enrollment = $course->enrollments()
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

    public function update(User $user, Course $course): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Course $course): bool
    {
        return $user->isAdmin();
    }
}
