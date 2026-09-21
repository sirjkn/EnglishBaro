<?php

namespace App\Listeners;

use App\Models\UserSession;
use Illuminate\Auth\Events\Logout;

class CloseUserSessionOnLogout
{
    public function handle(Logout $event): void
    {
        UserSession::query()
            ->where('session_id', session()->getId())
            ->update([
                'logout_at' => now(),
                'status' => 'terminated',
            ]);
    }
}
