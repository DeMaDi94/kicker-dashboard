<?php

declare(strict_types=1);

namespace App\Domain\Visits;

use DateTimeImmutable;

/**
 * VIS-04 — the periods the statistics offer: the last 7, 30, 90 or 365 days,
 * today included. 30 is preselected, and any other value opens it too (D14).
 */
enum VisitPeriod: int
{
    case Week = 7;
    case Month = 30;
    case Quarter = 90;
    case Year = 365;

    public static function fromDays(?int $days): self
    {
        return self::tryFrom($days ?? self::Month->value) ?? self::Month;
    }

    /**
     * The first day of the period, at midnight.
     */
    public function firstDay(DateTimeImmutable $today): DateTimeImmutable
    {
        return $today->setTime(0, 0)->modify('-'.($this->value - 1).' days');
    }
}
