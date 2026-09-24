<?php

declare(strict_types=1);

namespace App\Http\Seasons\ShowSeason;

use App\Domain\Matchdays\MatchdayPlaces;
use App\Domain\Matchdays\MatchdayResult;
use App\Domain\Matchdays\MatchdayRow;
use App\Domain\Seasons\SeasonLength;
use App\Domain\Standings\StandingRow;
use App\Domain\Statistics\PenaltyBox;
use App\Domain\Statistics\SeasonTimeline;
use App\Models\Player;
use App\Models\Score;
use App\Models\Season;

/**
 * ACC-01 — the public season view: the overall table with points and
 * penalty sums, each matchday's points, places and penalties, and the other
 * seasons to switch to.
 *
 * @phpstan-type SeasonOption array{id: int, name: string}
 * @phpstan-type StandingLine array{playerId: int, name: string, alias: string, place: int, points: int, penaltyCents: int, firstHalfPenaltyCents: int|null, secondHalfPenaltyCents: int|null}
 * @phpstan-type MatchdayLine array{playerId: int, name: string, alias: string, points: int|null, place: int|null, penaltyCents: int|null}
 * @phpstan-type MatchdayBlock array{number: int, complete: bool, hasPoints: bool, rows: list<MatchdayLine>, highlights: Highlights|null}
 * @phpstan-type Highlights array{winners: list<array{playerId: int, name: string}>, lanterns: list<array{playerId: int, name: string}>, average: float, penaltyCents: int}
 * @phpstan-type SeasonView array{seasons: list<SeasonOption>, season: array{id: int, name: string, penaltyStartCents: int, penaltyStepCents: int, settlementMatchday: int|null}|null, standings: list<StandingLine>, matchdays: list<MatchdayBlock>, penaltyBox: PenaltyBox|null}
 */
final class ShowSeasonService
{
    /**
     * @return SeasonView
     */
    public function __invoke(?Season $season): array
    {
        $seasons = Season::query()->latest('id')->get(['id', 'name']);

        // ACC-01 — without a choice, the season created last.
        $season ??= Season::query()->latest('id')->first();

        $options = array_values($seasons->map(fn (Season $each): array => ['id' => $each->id, 'name' => $each->name])->all());

        if ($season === null) {
            return ['seasons' => $options, 'season' => null, 'standings' => [], 'matchdays' => [], 'penaltyBox' => null];
        }

        $players = $season->players()->get(['players.id', 'players.name', 'players.alias'])->keyBy('id');
        $participants = $players->map(fn (Player $player): string => $player->name)->all();
        $alias = fn (int $id): string => $players->get($id)->alias ?? '';

        $pointsByMatchday = [];
        foreach ($season->scores()->get(['player_id', 'matchday', 'points']) as $score) {
            /** @var Score $score */
            $pointsByMatchday[$score->matchday][$score->player_id] = $score->points;
        }

        $scale = $season->penaltyScale();
        $timeline = new SeasonTimeline($participants, $pointsByMatchday, $scale, $season->settlement_matchday);

        $standings = array_map(fn (StandingRow $row): array => [
            'playerId' => $row->playerId,
            'name' => $row->name,
            'alias' => $alias($row->playerId),
            'place' => $row->place,
            'points' => $row->points,
            'penaltyCents' => $row->penaltyCents,
            'firstHalfPenaltyCents' => $row->firstHalfPenaltyCents,
            'secondHalfPenaltyCents' => $row->secondHalfPenaltyCents,
        ], $timeline->standings);

        $completed = [];
        foreach ($timeline->matchdays as $matchday) {
            $completed[$matchday->number] = $matchday;
        }

        $matchdays = array_map(fn (int $number): array => [
            'number' => $number,
            'complete' => MatchdayPlaces::isComplete(array_keys($participants), $pointsByMatchday[$number] ?? []),
            'hasPoints' => isset($pointsByMatchday[$number]),
            // STAT-11 — the day's winners, „Rote Laterne“ and league average;
            // STAT-13 — and the money that went into the box.
            'highlights' => isset($completed[$number]) ? [
                'winners' => $this->named($completed[$number]->winners(), $participants),
                'lanterns' => $this->named($completed[$number]->lanterns(), $participants),
                'average' => round($completed[$number]->average(), 1),
                'penaltyCents' => $completed[$number]->penaltyTotal(),
            ] : null,
            'rows' => array_map(fn (MatchdayRow $row): array => [
                'playerId' => $row->playerId,
                'name' => $row->name,
                'alias' => $alias($row->playerId),
                'points' => $row->points,
                'place' => $row->place,
                'penaltyCents' => $row->penaltyCents,
            ], MatchdayResult::of($participants, $pointsByMatchday[$number] ?? [], $scale)),
        ], SeasonLength::matchdays());

        return [
            'seasons' => $options,
            'season' => [
                'id' => $season->id,
                'name' => $season->name,
                'penaltyStartCents' => $season->penalty_start_cents,
                'penaltyStepCents' => $season->penalty_step_cents,
                'settlementMatchday' => $season->settlement_matchday,
            ],
            'standings' => $standings,
            'matchdays' => $matchdays,
            'penaltyBox' => PenaltyBox::of($timeline),
        ];
    }

    /**
     * @param  list<int>  $ids
     * @param  array<int, string>  $participants
     * @return list<array{playerId: int, name: string}>
     */
    private function named(array $ids, array $participants): array
    {
        return array_map(fn (int $id): array => ['playerId' => $id, 'name' => $participants[$id] ?? ''], $ids);
    }
}
