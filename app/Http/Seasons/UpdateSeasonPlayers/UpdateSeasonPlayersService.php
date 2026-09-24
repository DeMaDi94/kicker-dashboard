<?php

declare(strict_types=1);

namespace App\Http\Seasons\UpdateSeasonPlayers;

use App\Models\Season;

final class UpdateSeasonPlayersService
{
    /**
     * @param  list<int>  $playerIds
     */
    public function __invoke(Season $season, array $playerIds): void
    {
        $season->players()->sync($playerIds);
    }
}
