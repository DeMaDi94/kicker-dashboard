<?php

declare(strict_types=1);

namespace App\Http\Seasons\CreateSeason;

use App\Domain\Seasons\SeasonLength;
use App\Http\Players\Ports\PlayerListPort;
use Inertia\Inertia;
use Inertia\Response;

final class CreateSeasonController
{
    public function __invoke(PlayerListPort $players): Response
    {
        return Inertia::render('seasons/create', [
            'players' => $players->all(),
            'settlementMatchdays' => SeasonLength::settlementMatchdays(),
        ]);
    }
}
