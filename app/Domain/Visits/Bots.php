<?php

declare(strict_types=1);

namespace App\Domain\Visits;

/**
 * VIS-01 — bots and crawlers do not count. D14 — a request is a bot when it
 * sends no user agent, or one containing any of these words, in any case.
 * D18 — a messenger fetching a link preview is one too (`whatsapp`).
 */
final class Bots
{
    private const array WORDS = [
        'bot', 'crawl', 'spider', 'slurp', 'facebookexternalhit', 'preview',
        'monitor', 'headless', 'curl', 'wget', 'python', 'go-http-client',
        'whatsapp',
    ];

    public static function matches(?string $userAgent): bool
    {
        if ($userAgent === null || trim($userAgent) === '') {
            return true;
        }

        $agent = strtolower($userAgent);

        return array_any(self::WORDS, fn (string $word): bool => str_contains($agent, $word));
    }
}
