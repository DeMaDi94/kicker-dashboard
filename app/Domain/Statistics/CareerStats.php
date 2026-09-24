<?php

declare(strict_types=1);

namespace App\Domain\Statistics;

/**
 * STAT-08 — a player's all-time balance. The average place takes each season
 * with at least one complete matchday; before that a season has no table to
 * place anyone in (D11).
 */
final readonly class CareerStats
{
    public function __construct(
        public int $seasonsPlayed,
        public ?float $averagePlace,
        public int $totalPoints,
        public int $totalPenaltyCents,
        public int $totalWins,
    ) {}

    /**
     * @param  list<PlayerSeasonStats>  $seasons
     */
    public static function of(array $seasons): self
    {
        $placed = array_values(array_filter($seasons, fn (PlayerSeasonStats $season): bool => $season->matchdaysPlayed > 0));
        $places = array_map(fn (PlayerSeasonStats $season): int => $season->place, $placed);

        return new self(
            seasonsPlayed: count($seasons),
            averagePlace: $places === [] ? null : round(array_sum($places) / count($places), 1),
            totalPoints: array_sum(array_map(fn (PlayerSeasonStats $season): int => $season->totalPoints, $seasons)),
            totalPenaltyCents: array_sum(array_map(fn (PlayerSeasonStats $season): int => $season->penaltyCents, $seasons)),
            totalWins: array_sum(array_map(fn (PlayerSeasonStats $season): int => $season->wins, $seasons)),
        );
    }
}
