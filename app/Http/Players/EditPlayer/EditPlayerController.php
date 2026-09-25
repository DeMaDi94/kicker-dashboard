<?php

declare(strict_types=1);

namespace App\Http\Players\EditPlayer;

use App\Models\Player;
use Inertia\Inertia;
use Inertia\Response;

final class EditPlayerController
{
    public function __invoke(Player $player): Response
    {
        return Inertia::render('players/edit', [
            'player' => ['id' => $player->id, 'name' => $player->name, 'alias' => $player->alias],
        ]);
    }
}
