<?php

declare(strict_types=1);

namespace App\Http\Seasons\UpdatePenaltyScale;

use App\Models\Season;

final class UpdatePenaltyScaleService
{
    /**
     * SEA-05 — every matchday's penalty is derived from the scale on read, so
     * storing the new values is all it takes for the whole season to follow;
     * nothing is recomputed or stored per matchday.
     */
    public function __invoke(Season $season, int $penaltyStartCents, int $penaltyStepCents): void
    {
        $season->update([
            'penalty_start_cents' => $penaltyStartCents,
            'penalty_step_cents' => $penaltyStepCents,
        ]);
    }
}
