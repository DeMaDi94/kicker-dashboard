<?php

declare(strict_types=1);

namespace App\Http\Players\CreatePlayer;

use Inertia\Inertia;
use Inertia\Response;

final class CreatePlayerController
{
    public function __invoke(): Response
    {
        return Inertia::render('players/create');
    }
}
