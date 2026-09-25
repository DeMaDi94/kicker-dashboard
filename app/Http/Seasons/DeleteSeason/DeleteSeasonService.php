<?php

declare(strict_types=1);

namespace App\Http\Seasons\DeleteSeason;

use App\Domain\History\HistoryAction;
use App\Http\History\Ports\HistoryPort;
use App\Models\Season;
use Illuminate\Support\Facades\DB;

/**
 * SEA-06 — a soft delete: the season leaves every view and statistic, its
 * scores stay in place for a restore.
 */
final class DeleteSeasonService
{
    public function __construct(private HistoryPort $history) {}

    public function __invoke(Season $season): void
    {
        DB::transaction(function () use ($season): void {
            $season->delete();

            // LOG-01
            $this->history->record(HistoryAction::SeasonDeleted, $season->name, []);
        });
    }
}
