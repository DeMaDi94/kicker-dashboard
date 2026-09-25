<?php

declare(strict_types=1);

namespace App\Http\Seasons\UpdateSeasonSettlement;

use App\Domain\History\Change;
use App\Domain\History\HistoryAction;
use App\Http\History\Ports\HistoryPort;
use App\Models\Season;
use Illuminate\Support\Facades\DB;

final class UpdateSeasonSettlementService
{
    public function __construct(private HistoryPort $history) {}

    /**
     * PEN-04 — the halves are derived on read, so moving or removing the
     * settlement only moves the line; no penalty is lost.
     */
    public function __invoke(Season $season, ?int $settlementMatchday): void
    {
        DB::transaction(function () use ($season, $settlementMatchday): void {
            $before = ['settlementMatchday' => $season->settlement_matchday];

            $season->update(['settlement_matchday' => $settlementMatchday]);

            // LOG-01
            $this->history->record(HistoryAction::SettlementChanged, $season->name, Change::between($before, ['settlementMatchday' => $settlementMatchday]));
        });
    }
}
