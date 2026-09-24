<?php

declare(strict_types=1);

namespace App\Domain\Statistics;

/**
 * One player's line of one complete matchday, for the graphs (STAT-04–06).
 */
final readonly class MatchdayLine
{
    public function __construct(
        public int $matchday,
        public int $points,
        public float $leagueAverage,
        public int $dayPlace,
        public int $overallPlace,
        public int $penaltyCents,
        public int $cumulativePenaltyCents,
    ) {}
}
