<?php

declare(strict_types=1);

namespace App\Http\Seasons\RestoreSeason;

use App\Models\Season;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

final class RestoreSeasonController
{
    public function __invoke(Season $season, RestoreSeasonService $restore): RedirectResponse
    {
        if (! $restore($season)) {
            Inertia::flash('toast', ['type' => 'error', 'message' => __('Another season is already called :name, so this one cannot be restored.', ['name' => $season->name])]);

            return back();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Season :name restored.', ['name' => $season->name])]);

        return back();
    }
}
