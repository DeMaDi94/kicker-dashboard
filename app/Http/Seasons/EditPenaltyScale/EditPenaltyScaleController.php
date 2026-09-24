<?php

declare(strict_types=1);

namespace App\Http\Seasons\EditPenaltyScale;

use App\Models\Season;
use Inertia\Inertia;
use Inertia\Response;

/**
 * SEA-05 — change a season's start amount and step at any time.
 */
final class EditPenaltyScaleController
{
    public function __invoke(Season $season): Response
    {
        return Inertia::render('seasons/penalty-scale', [
            'season' => [
                'id' => $season->id,
                'name' => $season->name,
                'penaltyStartCents' => $season->penalty_start_cents,
                'penaltyStepCents' => $season->penalty_step_cents,
            ],
        ]);
    }
}
