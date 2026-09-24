<?php

declare(strict_types=1);

namespace App\Domain\Statistics;

use App\Domain\Matchdays\MatchdayPlaces;
use App\Domain\Penalties\PenaltyScale;
use App\Domain\Standings\StandingRow;
use App\Domain\Standings\Standings;

/**
 * A season as the statistics read it: its complete matchdays in order
 * (STAT-02) and the overall table at the end.
 */
final readonly class SeasonTimeline
{
    /** @var list<CompletedMatchday> */
    public array $matchdays;

    /** @var list<StandingRow> */
    public array $standings;

    /**
     * @param  array<int, string>  $participants  player id => name
     * @param  array<int, array<int, int>>  $pointsByMatchday  matchday => (player id => points)
     */
    public function __construct(
        public array $participants,
        array $pointsByMatchday,
        public PenaltyScale $scale,
        public ?int $settlementMatchday = null,
    ) {
        ksort($pointsByMatchday);
        $ids = array_keys($participants);
        $complete = [];
        $matchdays = [];

        foreach ($pointsByMatchday as $number => $points) {
            $points = array_intersect_key($points, $participants);

            if (! MatchdayPlaces::isComplete($ids, $points)) {
                continue;
            }

            $complete[$number] = $points;
            $overall = [];
            foreach (Standings::of($participants, $complete, $scale) as $row) {
                $overall[$row->playerId] = $row->place;
            }

            $matchdays[] = new CompletedMatchday(
                $number,
                $points,
                MatchdayPlaces::of($points),
                $scale->penalties($points),
                $overall,
            );
        }

        $this->matchdays = $matchdays;
        $this->standings = Standings::of($participants, $complete, $scale, $settlementMatchday);
    }

    public function standingOf(int $playerId): ?StandingRow
    {
        foreach ($this->standings as $row) {
            if ($row->playerId === $playerId) {
                return $row;
            }
        }

        return null;
    }
}
