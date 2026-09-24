<?php

declare(strict_types=1);

namespace App\Http\Seasons\ShowSeason;

use App\Domain\Matchdays\MatchdayPlaces;
use App\Domain\Matchdays\MatchdayResult;
use App\Domain\Matchdays\MatchdayRow;
use App\Domain\Seasons\SeasonLength;
use App\Domain\Standings\StandingRow;
use App\Domain\Standings\Standings;
use App\Models\Player;
use App\Models\Score;
use App\Models\Season;

/**
 * ACC-01 — the public season view: the overall table with points and
 * penalty sums, each matchday's points, places and penalties, and the other
 * seasons to switch to.
 *
 * @phpstan-type SeasonOption array{id: int, name: string}
 * @phpstan-type StandingLine array{playerId: int, name: string, alias: string, place: int, points: int, penaltyCents: int}
 * @phpstan-type MatchdayLine array{playerId: int, name: string, alias: string, points: int|null, place: int|null, penaltyCents: int|null}
 * @phpstan-type MatchdayBlock array{number: int, complete: bool, hasPoints: bool, rows: list<MatchdayLine>}
 * @phpstan-type SeasonView array{seasons: list<SeasonOption>, season: array{id: int, name: string, penaltyStartCents: int, penaltyStepCents: int}|null, standings: list<StandingLine>, matchdays: list<MatchdayBlock>}
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
            return ['seasons' => $options, 'season' => null, 'standings' => [], 'matchdays' => []];
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

        $standings = array_map(fn (StandingRow $row): array => [
            'playerId' => $row->playerId,
            'name' => $row->name,
            'alias' => $alias($row->playerId),
            'place' => $row->place,
            'points' => $row->points,
            'penaltyCents' => $row->penaltyCents,
        ], Standings::of($participants, $pointsByMatchday, $scale));

        $matchdays = array_map(fn (int $number): array => [
            'number' => $number,
            'complete' => MatchdayPlaces::isComplete(array_keys($participants), $pointsByMatchday[$number] ?? []),
            'hasPoints' => isset($pointsByMatchday[$number]),
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
            ],
            'standings' => $standings,
            'matchdays' => $matchdays,
        ];
    }
}
