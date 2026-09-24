<?php

declare(strict_types=1);

namespace App\Domain\Matchdays;

/**
 * One player's line of a matchday. Place and penalty stay null until the
 * matchday is complete (MD-02).
 */
final readonly class MatchdayRow
{
    public function __construct(
        public int $playerId,
        public string $name,
        public ?int $points,
        public ?int $place,
        public ?int $penaltyCents,
    ) {}
}
