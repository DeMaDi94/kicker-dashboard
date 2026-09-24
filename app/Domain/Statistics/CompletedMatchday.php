<?php

declare(strict_types=1);

namespace App\Domain\Statistics;

/**
 * STAT-02 — one complete matchday, with everything the statistics read from
 * it: points, the day's places and penalties, the overall table's places
 * after it, and the league average.
 */
final readonly class CompletedMatchday
{
    /**
     * @param  array<int, int>  $points  player id => points
     * @param  array<int, int>  $places  player id => place on the day (MD-03)
     * @param  array<int, int>  $penalties  player id => cents (PEN-01)
     * @param  array<int, int>  $overallPlaces  player id => place in the overall table after this matchday (STD-01)
     */
    public function __construct(
        public int $number,
        public array $points,
        public array $places,
        public array $penalties,
        public array $overallPlaces,
    ) {}

    /**
     * STAT-04 / STAT-11 — the mean points of every player of the season.
     */
    public function average(): float
    {
        return $this->points === [] ? 0.0 : array_sum($this->points) / count($this->points);
    }

    /**
     * STAT-10 — the gap between the day's highest and lowest points.
     */
    public function spread(): int
    {
        return $this->points === [] ? 0 : max($this->points) - min($this->points);
    }

    public function lastPlace(): int
    {
        return $this->places === [] ? 0 : max($this->places);
    }

    /**
     * STAT-07 / STAT-11 — first place; a tie counts for each.
     *
     * @return list<int>
     */
    public function winners(): array
    {
        return array_keys(array_filter($this->places, fn (int $place): bool => $place === 1));
    }

    /**
     * STAT-07 / STAT-11 — „Rote Laterne“: last place; a tie counts for each.
     *
     * @return list<int>
     */
    public function lanterns(): array
    {
        $last = $this->lastPlace();

        return array_keys(array_filter($this->places, fn (int $place): bool => $place === $last));
    }
}
