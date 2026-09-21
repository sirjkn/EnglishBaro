<?php

namespace App\Listeners;

use App\Models\LoginAttempt;
use App\Models\User;
use App\Models\UserSession;
use App\Services\UserAgentParser;
use Illuminate\Auth\Events\Login;

class CreateUserSessionOnLogin
{
    public function __construct(
        private readonly UserAgentParser $userAgentParser,
    ) {}

    public function handle(Login $event): void
    {
        /** @var User $user */
        $user = $event->user;

        $userAgent = request()->userAgent();
        $parsed = $this->userAgentParser->parse($userAgent);

        $user->forceFill(['last_login_at' => now()])->save();

        LoginAttempt::create([
            'email' => $user->email,
            'ip_address' => request()->ip(),
            'successful' => true,
            'user_agent' => $userAgent,
        ]);

        UserSession::query()->updateOrCreate(
            ['session_id' => session()->getId()],
            [
                'user_id' => $user->id,
                'device' => $parsed['device'],
                'browser' => $parsed['browser'],
                'browser_version' => $parsed['browser_version'],
                'platform' => $parsed['platform'],
                'ip_address' => request()->ip(),
                'user_agent' => $userAgent,
                'login_at' => now(),
                'last_activity_at' => now(),
                'logout_at' => null,
                'status' => 'active',
            ]
        );
    }
}
