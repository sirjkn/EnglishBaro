<?php

namespace App\Policies;

use App\Models\User;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, User $student): bool
    {
        return $user->isAdmin() || $user->id === $student->id;
    }

    public function update(User $user, User $student): bool
    {
        return $user->isAdmin() || $user->id === $student->id;
    }

    public function delete(User $user, User $student): bool
    {
        return $user->isAdmin();
    }
}
