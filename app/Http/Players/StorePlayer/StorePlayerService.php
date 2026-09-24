<?php

declare(strict_types=1);

namespace App\Http\Players\StorePlayer;

use App\Models\Player;

final class StorePlayerService
{
    public function __invoke(string $name, string $alias): Player
    {
        return Player::create(['name' => $name, 'alias' => $alias]);
    }
}
