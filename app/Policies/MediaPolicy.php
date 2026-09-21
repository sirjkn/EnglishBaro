<?php

namespace App\Policies;

use App\Models\Media;
use App\Models\User;

class MediaPolicy
{
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Media $media): bool
    {
        return $user->isAdmin();
    }
}
