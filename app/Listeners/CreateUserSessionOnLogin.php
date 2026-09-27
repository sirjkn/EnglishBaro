<?php

namespace App\Listeners;

use App\Models\LoginAttempt;
use App\Models\User;
use App\Models\UserSession;
use App\Services\GeoLocationService;
use App\Services\UserAgentParser;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class CreateUserSessionOnLogin
{
    private const DEVICE_COOKIE = 'eb_device_id';

    public function __construct(
        private readonly UserAgentParser $userAgentParser,
        private readonly GeoLocationService $geoLocationService,
    ) {}

    public function handle(Login $event): void
    {
        /** @var User $user */
        $user = $event->user;

        $userAgent = request()->userAgent();
        $parsed = $this->userAgentParser->parse($userAgent);
        $ip = request()->ip();

        $user->forceFill(['last_login_at' => now()])->save();

        LoginAttempt::create([
            'email' => $user->email,
            'ip_address' => $ip,
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
                'ip_address' => $ip,
                'location' => $this->geoLocationService->locationForIp($ip),
                'user_agent' => $userAgent,
                'device_token' => $this->resolveDeviceToken(),
                'login_at' => now(),
                'last_activity_at' => now(),
                'logout_at' => null,
                'status' => 'active',
            ]
        );
    }

    /**
     * A MAC address is never visible over HTTP, so this cookie is the closest
     * real equivalent: a token that survives across logins/logouts on the
     * same browser, letting admins recognize a returning device.
     */
    private function resolveDeviceToken(): string
    {
        $token = request()->cookie(self::DEVICE_COOKIE);

        if (! $token) {
            $token = (string) Str::uuid();
            Cookie::queue(self::DEVICE_COOKIE, $token, 60 * 24 * 400);
        }

        return $token;
    }
}
