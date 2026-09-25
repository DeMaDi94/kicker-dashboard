<?php

declare(strict_types=1);

namespace App\Http\Seasons\UpdatePenaltyScale;

use App\Domain\History\Change;
use App\Domain\History\HistoryAction;
use App\Http\History\Ports\HistoryPort;
use App\Models\Season;
use Illuminate\Support\Facades\DB;

final class UpdatePenaltyScaleService
{
    public function __construct(private HistoryPort $history) {}

    /**
     * SEA-05 — every matchday's penalty is derived from the scale on read, so
     * storing the new values is all it takes for the whole season to follow;
     * nothing is recomputed or stored per matchday.
     */
    public function __invoke(Season $season, int $penaltyStartCents, int $penaltyStepCents): void
    {
        DB::transaction(function () use ($season, $penaltyStartCents, $penaltyStepCents): void {
            $before = ['penaltyStart' => $season->penalty_start_cents, 'penaltyStep' => $season->penalty_step_cents];

            $season->update([
                'penalty_start_cents' => $penaltyStartCents,
                'penalty_step_cents' => $penaltyStepCents,
            ]);

            // LOG-01
            $this->history->record(HistoryAction::PenaltyScaleChanged, $season->name, Change::between($before, [
                'penaltyStart' => $penaltyStartCents,
                'penaltyStep' => $penaltyStepCents,
            ]));
        });
    }
}
