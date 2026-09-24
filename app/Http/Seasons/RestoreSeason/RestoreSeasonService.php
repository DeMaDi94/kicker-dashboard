<?php

declare(strict_types=1);

namespace App\Http\Seasons\RestoreSeason;

use App\Models\Season;

/**
 * SEA-06 — a deleted season comes back with its players and scores, which
 * the delete left in place. D8 — not while another season carries its name,
 * which deleting it had freed.
 */
final class RestoreSeasonService
{
    public function __invoke(Season $season): bool
    {
        if (Season::query()->where('name', $season->name)->exists()) {
            return false;
        }

        return $season->restore();
    }
}
