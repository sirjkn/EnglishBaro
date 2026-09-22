<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeoLocationService
{
    public const REGIONS = ['Africa', 'Europe', 'North America', 'Asia'];

    private const CACHE_TTL_HOURS = 12;

    /**
     * Resolve a request IP address to one of the four pricing regions.
     * Returns null when the region can't be determined (e.g. private/local IPs
     * without a debug override), so callers can fall back to base pricing.
     */
    public function regionForIp(string $ip): ?string
    {
        if ($this->isPrivateOrReserved($ip)) {
            return config('geo.dev_fallback_region');
        }

        return Cache::remember("geo:region:{$ip}", now()->addHours(self::CACHE_TTL_HOURS), function () use ($ip) {
            return $this->lookup($ip);
        });
    }

    private function lookup(string $ip): ?string
    {
        try {
            $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}", [
                'fields' => 'status,continent',
            ]);

            if (! $response->ok() || $response->json('status') !== 'success') {
                return null;
            }

            $continent = $response->json('continent');

            return in_array($continent, self::REGIONS, true) ? $continent : null;
        } catch (\Throwable $e) {
            Log::warning('GeoLocationService lookup failed', ['ip' => $ip, 'error' => $e->getMessage()]);

            return null;
        }
    }

    private function isPrivateOrReserved(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }
}
