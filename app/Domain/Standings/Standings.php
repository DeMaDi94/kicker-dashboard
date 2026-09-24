<?php

declare(strict_types=1);

namespace App\Domain\Standings;

use App\Domain\Matchdays\MatchdayPlaces;
use App\Domain\Penalties\PenaltyScale;
use App\Domain\Shared\NameOrder;

/**
 * The overall table of a season.
 */
final class Standings
{
    /**
     * STD-01 — ordered by the sum of points over the complete matchdays, most
     * first; equal sums share a place, counted densely (D5). Within a place
     * the lower penalty sum comes first, then the name A–Z (D3).
     * PEN-03 — each row carries the player's penalty sum of the season.
     *
     * @param  array<int, string>  $participants  player id => name
     * @param  array<int, array<int, int>>  $pointsByMatchday  matchday => (player id => points)
     * @return list<StandingRow>
     */
    public static function of(array $participants, array $pointsByMatchday, PenaltyScale $scale): array
    {
        $points = array_fill_keys(array_keys($participants), 0);
        $penalties = $points;
        $ids = array_keys($participants);

        foreach ($pointsByMatchday as $matchday) {
            // STD-01 / MD-02 — only complete matchdays count.
            if (! MatchdayPlaces::isComplete($ids, $matchday)) {
                continue;
            }

            $matchday = array_intersect_key($matchday, $participants);

            foreach ($scale->penalties($matchday) as $id => $cents) {
                $points[$id] += $matchday[$id];
                $penalties[$id] += $cents;
            }
        }

        $places = MatchdayPlaces::of($points);

        $rows = array_map(
            fn (int $id): StandingRow => new StandingRow($id, $participants[$id], $places[$id], $points[$id], $penalties[$id]),
            $ids,
        );

        usort($rows, fn (StandingRow $a, StandingRow $b): int => [$a->place, $a->penaltyCents] <=> [$b->place, $b->penaltyCents]
            ?: NameOrder::compare($a->name, $b->name));

        return $rows;
    }
}
