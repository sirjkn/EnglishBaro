<?php

namespace App\Listeners;

use App\Models\LoginAttempt;
use Illuminate\Auth\Events\Failed;

class RecordLoginAttempt
{
    public function handle(Failed $event): void
    {
        LoginAttempt::create([
            'email' => $event->credentials['email'] ?? null,
            'ip_address' => request()->ip(),
            'successful' => false,
            'user_agent' => request()->userAgent(),
        ]);
    }
}
