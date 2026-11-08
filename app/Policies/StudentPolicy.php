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

    /**
     * Gates for managing admin/staff accounts (Admin > Users), as opposed to
     * the student-account methods above — both operate on the same User
     * model, which is why they live in this policy rather than a separate
     * one Laravel would refuse to also bind to User::class.
     *
     * Granting a staff account the "users" module lets them manage *other*
     * staff accounts, but never admin accounts and never their own — closing
     * the privilege-escalation path where a staff member with that module
     * could otherwise create a new admin account or promote themselves.
     */
    public function manageStaff(User $user): bool
    {
        return $user->isAdmin() || $user->hasModule('users');
    }

    public function editStaffAccount(User $user, User $target): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (! $user->hasModule('users')) {
            return false;
        }

        return $target->user_type !== 'admin';
    }
}
