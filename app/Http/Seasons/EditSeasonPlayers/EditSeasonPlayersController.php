<?php

declare(strict_types=1);

namespace App\Http\Seasons\EditSeasonPlayers;

use App\Http\Players\Ports\PlayerListPort;
use App\Http\Seasons\Shared\SeasonLock;
use App\Models\Season;
use Inertia\Inertia;
use Inertia\Response;

final class EditSeasonPlayersController
{
    public function __invoke(Season $season, PlayerListPort $players, SeasonLock $lock, EditSeasonPlayersService $selected): Response
    {
        return Inertia::render('seasons/players', [
            'season' => ['id' => $season->id, 'name' => $season->name],
            'players' => $players->all(),
            'selected' => $selected($season),
            'locked' => $lock($season),
        ]);
    }
}
