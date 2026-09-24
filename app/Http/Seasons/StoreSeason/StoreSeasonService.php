<?php

declare(strict_types=1);

namespace App\Http\Seasons\StoreSeason;

use App\Models\Season;
use Illuminate\Support\Facades\DB;

final class StoreSeasonService
{
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

            return $season;
        });
    }
}
