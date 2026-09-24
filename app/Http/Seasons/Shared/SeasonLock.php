<?php

declare(strict_types=1);

namespace App\Http\Seasons\Shared;

use App\Models\Season;

/**
 * SEA-03 — a season's players are fixed from the first entered point on.
 */
final class SeasonLock
{
    public function __invoke(Season $season): bool
    {
        return $season->scores()->exists();
    }
}
