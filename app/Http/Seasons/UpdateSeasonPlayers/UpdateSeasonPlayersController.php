<?php

declare(strict_types=1);

namespace App\Http\Seasons\UpdateSeasonPlayers;

use App\Models\Season;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

final class UpdateSeasonPlayersController
{
    public function __invoke(Season $season, UpdateSeasonPlayersRequest $request, UpdateSeasonPlayersService $update): RedirectResponse
    {
        $update($season, $request->playerIds());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Players of the season saved.')]);

        return to_route('seasons.show', $season);
    }
}
