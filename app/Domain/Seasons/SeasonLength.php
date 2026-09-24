<?php

declare(strict_types=1);

namespace App\Domain\Seasons;

/**
 * SEA-04 — a season has 34 matchdays, as the Bundesliga does. Fixed by the
 * spec, not configurable.
 */
final class SeasonLength
{
    public const int MATCHDAYS = 34;

    /**
     * @return list<int>
     */
    public static function matchdays(): array
    {
        return range(1, self::MATCHDAYS);
    }

    /**
     * PEN-04 — the matchdays an interim settlement can follow: any but the
     * last, so both halves hold at least one matchday.
     *
     * @return list<int>
     */
    public static function settlementMatchdays(): array
    {
        return range(1, self::MATCHDAYS - 1);
    }
}
