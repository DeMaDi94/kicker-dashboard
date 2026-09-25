<?php

declare(strict_types=1);

namespace App\Http\Seasons\StoreSeason;

use App\Domain\History\Change;
use App\Domain\History\HistoryAction;
use App\Http\History\Ports\HistoryPort;
use App\Models\Player;
use App\Models\Season;
use Illuminate\Support\Facades\DB;

final class StoreSeasonService
{
    public function __construct(private HistoryPort $history) {}

    public function __invoke(StoreSeasonInput $input): Season
    {
        return DB::transaction(function () use ($input): Season {
            $season = Season::create([
                'name' => $input->name,
                'penalty_start_cents' => $input->penaltyStartCents,
                'penalty_step_cents' => $input->penaltyStepCents,
                'settlement_matchday' => $input->settlementMatchday,
            ]);

            $season->players()->sync($input->playerIds);

            // LOG-01
            /** @var list<string> $players */
            $players = Player::query()->whereKey($input->playerIds)->pluck('name')->all();
            $this->history->record(HistoryAction::SeasonCreated, $season->name, [
                ...Change::between([], [
                    'penaltyStart' => $input->penaltyStartCents,
                    'penaltyStep' => $input->penaltyStepCents,
                    'settlementMatchday' => $input->settlementMatchday,
                ]),
                ...Change::players([], $players),
            ]);

            return $season;
        });
    }
}
