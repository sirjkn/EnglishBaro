<?php

namespace App\Policies;

use App\Models\Assessment;
use App\Models\User;

class AssessmentPolicy
{
    public function view(User $user, Assessment $assessment): bool
    {
        return app(CoursePolicy::class)->learn($user, $assessment->course);
    }

    public function attempt(User $user, Assessment $assessment): bool
    {
        return app(CoursePolicy::class)->learn($user, $assessment->course);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Assessment $assessment): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Assessment $assessment): bool
    {
        return $user->isAdmin();
    }
}
