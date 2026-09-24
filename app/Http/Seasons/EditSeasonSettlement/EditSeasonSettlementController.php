<?php

declare(strict_types=1);

namespace App\Http\Seasons\EditSeasonSettlement;

use App\Domain\Seasons\SeasonLength;
use App\Models\Season;
use Inertia\Inertia;
use Inertia\Response;

/**
 * PEN-04 — change or remove the matchday the penalty box is settled after.
 */
final class EditSeasonSettlementController
{
    public function __invoke(Season $season): Response
    {
        return Inertia::render('seasons/settlement', [
            'season' => ['id' => $season->id, 'name' => $season->name, 'settlementMatchday' => $season->settlement_matchday],
            'matchdays' => SeasonLength::settlementMatchdays(),
        ]);
    }
}
