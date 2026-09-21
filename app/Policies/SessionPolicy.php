<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserSession;

class SessionPolicy
{
    public function view(User $user, UserSession $session): bool
    {
        return $user->isAdmin() || $user->id === $session->user_id;
    }

    public function terminate(User $user, UserSession $session): bool
    {
        return $user->isAdmin() || $user->id === $session->user_id;
    }
}
