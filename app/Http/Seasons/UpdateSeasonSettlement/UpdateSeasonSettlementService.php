<?php

declare(strict_types=1);

namespace App\Http\Seasons\UpdateSeasonSettlement;

use App\Models\Season;

final class UpdateSeasonSettlementService
{
    /**
     * PEN-04 — the halves are derived on read, so moving or removing the
     * settlement only moves the line; no penalty is lost.
     */
    public function __invoke(Season $season, ?int $settlementMatchday): void
    {
        $season->update(['settlement_matchday' => $settlementMatchday]);
    }
}
