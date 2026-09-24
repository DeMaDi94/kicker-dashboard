<?php

declare(strict_types=1);

namespace App\Domain\Matchdays;

/**
 * The places of one matchday, computed from the points.
 */
final class MatchdayPlaces
{
    /**
     * MD-02 — a matchday is complete once every player of the season has
     * points for it.
     *
     * @param  list<int>  $participantIds
     * @param  array<int, int>  $points  player id => points
     */
    public static function isComplete(array $participantIds, array $points): bool
    {
        if ($participantIds === []) {
            return false;
        }

        foreach ($participantIds as $id) {
            if (! array_key_exists($id, $points)) {
                return false;
            }
        }

        return true;
    }

    /**
     * MD-03 — more points, better place; equal points, equal place; places
     * are counted densely: 1, 2, 3, 3, 4.
     *
     * @param  array<int, int>  $points  player id => points
     * @return array<int, int> player id => place
     */
    public static function of(array $points): array
    {
        $distinct = array_values(array_unique($points));
        rsort($distinct);
        $placeOf = array_flip($distinct);

        return array_map(fn (int $each): int => $placeOf[$each] + 1, $points);
    }
}
