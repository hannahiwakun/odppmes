<?php

namespace App\Cms;

final class CmsSafeHttpUrl
{
    public static function sanitize(?string $url): ?string
    {
        $candidate = trim((string) $url);
        if ($candidate === '') {
            return null;
        }

        if (! preg_match('#^https?://#i', $candidate)) {
            return null;
        }

        if (filter_var($candidate, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $scheme = strtolower((string) parse_url($candidate, PHP_URL_SCHEME));
        if (! in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        $host = (string) parse_url($candidate, PHP_URL_HOST);
        if ($host === '') {
            return null;
        }

        return $candidate;
    }

    public static function isExternal(?string $url): bool
    {
        $safe = self::sanitize($url);
        if ($safe === null) {
            return false;
        }

        $linkHost = strtolower((string) parse_url($safe, PHP_URL_HOST));
        $appHost = strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));

        if ($linkHost === '' || $appHost === '') {
            return true;
        }

        return $linkHost !== $appHost;
    }
}
