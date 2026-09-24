<?php

declare(strict_types=1);

namespace App\Http\Seasons\DeleteSeason;

use App\Models\Season;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

final class DeleteSeasonController
{
    public function __invoke(Season $season, DeleteSeasonService $delete): RedirectResponse
    {
        $delete($season);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Season :name deleted.', ['name' => $season->name])]);

        return to_route('home');
    }
}
