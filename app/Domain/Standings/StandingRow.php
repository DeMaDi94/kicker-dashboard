<?php

declare(strict_types=1);

namespace App\Domain\Standings;

final readonly class StandingRow
{
    public function __construct(
        public int $playerId,
        public string $name,
        public int $place,
        public int $points,
        public int $penaltyCents,
        /** PEN-04 — null while the season has no interim settlement. */
        public ?int $firstHalfPenaltyCents = null,
        public ?int $secondHalfPenaltyCents = null,
    ) {}
}
