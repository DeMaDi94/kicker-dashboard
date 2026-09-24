<?php

declare(strict_types=1);

namespace App\Domain\Matchdays;

use App\Domain\Penalties\PenaltyScale;
use App\Domain\Shared\NameOrder;

/**
 * The result of one matchday as the season view shows it.
 */
final class MatchdayResult
{
    /**
     * MD-02 — places and penalties only once the matchday is complete.
     * MD-03 / PEN-01 — computed from the points. Ordered by place, then name
     * A–Z (D3); before completion, by name alone.
     *
     * @param  array<int, string>  $participants  player id => name
     * @param  array<int, int>  $points  player id => points
     * @return list<MatchdayRow>
     */
    public static function of(array $participants, array $points, PenaltyScale $scale): array
    {
        $points = array_intersect_key($points, $participants);
        $complete = MatchdayPlaces::isComplete(array_keys($participants), $points);
        $places = $complete ? MatchdayPlaces::of($points) : [];
        $penalties = $complete ? $scale->penalties($points) : [];

        $rows = [];

        foreach ($participants as $id => $name) {
            $rows[] = new MatchdayRow($id, $name, $points[$id] ?? null, $places[$id] ?? null, $penalties[$id] ?? null);
        }

        usort($rows, fn (MatchdayRow $a, MatchdayRow $b): int => ($a->place ?? 0) <=> ($b->place ?? 0)
            ?: NameOrder::compare($a->name, $b->name));

        return $rows;
    }
}
