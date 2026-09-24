<?php

declare(strict_types=1);

namespace App\Http\Seasons\RestoreSeason;

use App\Models\Season;

/**
 * SEA-06 — a deleted season comes back with its players and scores, which
 * the delete left in place.
 */
final class RestoreSeasonService
{
    public function __invoke(Season $season): void
    {
        $season->restore();
    }
}
