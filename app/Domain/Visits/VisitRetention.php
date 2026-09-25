<?php

declare(strict_types=1);

namespace App\Domain\Visits;

use DateTimeImmutable;

/**
 * VIS-03 — visits older than 12 months are deleted.
 */
final class VisitRetention
{
    public static function cutoff(DateTimeImmutable $now): DateTimeImmutable
    {
        return $now->modify('-12 months');
    }
}
