<?php

declare(strict_types=1);

namespace App\Domain\Visits;

/**
 * VIS-02, D14 — what tells the visitors of one day apart: SHA-256 of the
 * day's salt with the IP and the user agent, cut to 16 hex digits. Neither
 * the IP nor the user agent is kept, and without the salt, which is never
 * stored with the visits, a mark cannot be traced back once the day is over.
 */
final class VisitorMark
{
    public static function of(string $salt, string $ip, string $userAgent): string
    {
        return substr(hash('sha256', $salt."\n".$ip."\n".$userAgent), 0, 16);
    }
}
