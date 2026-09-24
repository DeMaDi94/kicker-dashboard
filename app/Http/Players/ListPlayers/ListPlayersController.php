<?php

declare(strict_types=1);

namespace App\Http\Players\ListPlayers;

use Inertia\Inertia;
use Inertia\Response;

final class ListPlayersController
{
    public function __invoke(ListPlayersService $list): Response
    {
        return Inertia::render('players/index', ['players' => $list()]);
    }
}
