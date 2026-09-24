<?php

declare(strict_types=1);

namespace App\Http\Seasons\EditSeasonPlayers;

use App\Models\Season;

final class EditSeasonPlayersService
{
    /**
     * @return list<int>
     */
    public function __invoke(Season $season): array
    {
        return array_values($season->players()->pluck('players.id')->map(fn (mixed $id): int => (int) $id)->all());
    }
}
