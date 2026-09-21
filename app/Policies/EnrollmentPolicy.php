<?php

namespace App\Policies;

use App\Models\Enrollment;
use App\Models\User;

class EnrollmentPolicy
{
    public function view(User $user, Enrollment $enrollment): bool
    {
        return $user->isAdmin() || $user->id === $enrollment->user_id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isStudent();
    }

    public function update(User $user, Enrollment $enrollment): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Enrollment $enrollment): bool
    {
        return $user->isAdmin();
    }
}
