<?php

namespace App\Services;

class UserAgentParser
{
    public function parse(?string $userAgent): array
    {
        $userAgent ??= '';

        return [
            'device' => $this->detectDevice($userAgent),
            'browser' => $this->detectBrowser($userAgent),
            'browser_version' => $this->detectBrowserVersion($userAgent),
            'platform' => $this->detectPlatform($userAgent),
        ];
    }

    private function detectDevice(string $userAgent): string
    {
        if (preg_match('/iPad|Tablet/i', $userAgent)) {
            return 'Tablet';
        }

        if (preg_match('/Mobile|iPhone|Android.*Mobile/i', $userAgent)) {
            return 'Mobile';
        }

        return 'Desktop';
    }

    private function detectBrowser(string $userAgent): string
    {
        return match (true) {
            (bool) preg_match('/Edg\//i', $userAgent) => 'Edge',
            (bool) preg_match('/OPR\//i', $userAgent) => 'Opera',
            (bool) preg_match('/Chrome\//i', $userAgent) => 'Chrome',
            (bool) preg_match('/Firefox\//i', $userAgent) => 'Firefox',
            (bool) preg_match('/Safari\//i', $userAgent) && preg_match('/Version\//i', $userAgent) => 'Safari',
            default => 'Unknown',
        };
    }

    private function detectBrowserVersion(string $userAgent): ?string
    {
        $patterns = [
            'Edge' => '/Edg\/([\d.]+)/i',
            'Opera' => '/OPR\/([\d.]+)/i',
            'Chrome' => '/Chrome\/([\d.]+)/i',
            'Firefox' => '/Firefox\/([\d.]+)/i',
            'Safari' => '/Version\/([\d.]+)/i',
        ];

        $browser = $this->detectBrowser($userAgent);

        if (! isset($patterns[$browser])) {
            return null;
        }

        return preg_match($patterns[$browser], $userAgent, $matches) ? $matches[1] : null;
    }

    private function detectPlatform(string $userAgent): string
    {
        return match (true) {
            (bool) preg_match('/Windows/i', $userAgent) => 'Windows',
            (bool) preg_match('/Mac OS X/i', $userAgent) => 'macOS',
            (bool) preg_match('/Android/i', $userAgent) => 'Android',
            (bool) preg_match('/iPhone|iPad|iOS/i', $userAgent) => 'iOS',
            (bool) preg_match('/Linux/i', $userAgent) => 'Linux',
            default => 'Unknown',
        };
    }
}
