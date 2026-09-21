<?php

namespace App\Support;

class SafeRedirect
{
    /**
     * Only allow redirecting to a same-app relative path, never an absolute
     * or external URL, to prevent open-redirect vulnerabilities.
     */
    public static function resolve(?string $target, string $default): string
    {
        if (! $target) {
            return $default;
        }

        if (! str_starts_with($target, '/') || str_starts_with($target, '//')) {
            return $default;
        }

        if (str_contains($target, '://')) {
            return $default;
        }

        return $target;
    }
}
