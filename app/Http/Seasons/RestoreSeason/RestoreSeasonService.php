<?php

declare(strict_types=1);

namespace App\Http\Seasons\RestoreSeason;

use App\Domain\History\HistoryAction;
use App\Http\History\Ports\HistoryPort;
use App\Models\Season;
use Illuminate\Support\Facades\DB;

/**
 * SEA-06 — a deleted season comes back with its players and scores, which
 * the delete left in place. D8 — not while another season carries its name,
 * which deleting it had freed.
 */
final class RestoreSeasonService
{
    public function __construct(private HistoryPort $history) {}

    public function __invoke(Season $season): bool
    {
        if (Season::query()->where('name', $season->name)->exists()) {
            return false;
        }

        return DB::transaction(function () use ($season): bool {
            $restored = $season->restore();

            if ($restored) {
                // LOG-01
                $this->history->record(HistoryAction::SeasonRestored, $season->name, []);
            }

            return $restored;
        });
    }
}
