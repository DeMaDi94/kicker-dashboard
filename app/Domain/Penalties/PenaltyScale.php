<?php

declare(strict_types=1);

namespace App\Domain\Penalties;

use InvalidArgumentException;

/**
 * PEN-01 — the season's penalty scale, in cents: the lowest score of a
 * matchday pays the start amount, each next higher score one step less, and
 * no amount falls below 0 €.
 */
final readonly class PenaltyScale
{
    public function __construct(
        public int $startCents,
        public int $stepCents,
    ) {
        if ($startCents < 0 || $stepCents < 0) {
            throw new InvalidArgumentException('A penalty scale holds no negative amount.');
        }
    }

    /**
     * PEN-01 — counted over the places, not the players: players with equal
     * points pay the same, and the next higher score is one step less.
     *
     * @param  array<int, int>  $points  player id => points of a complete matchday
     * @return array<int, int> player id => penalty in cents
     */
    public function penalties(array $points): array
    {
        $distinct = array_values(array_unique($points));
        sort($distinct);
        $fromBottom = array_flip($distinct);

        return array_map(
            fn (int $each): int => max(0, $this->startCents - $fromBottom[$each] * $this->stepCents),
            $points,
        );
    }
}
