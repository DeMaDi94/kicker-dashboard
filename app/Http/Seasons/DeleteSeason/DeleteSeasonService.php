<?php

declare(strict_types=1);

namespace App\Http\Seasons\DeleteSeason;

use App\Models\Season;

/**
 * SEA-06 — a soft delete: the season leaves every view and statistic, its
 * scores stay in place for a restore.
 */
final class DeleteSeasonService
{
    public function __invoke(Season $season): void
    {
        $season->delete();
    }
}
