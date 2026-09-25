<?php

declare(strict_types=1);

namespace App\Http\Players\StorePlayer;

use App\Domain\History\Change;
use App\Domain\History\HistoryAction;
use App\Http\History\Ports\HistoryPort;
use App\Models\Player;
use Illuminate\Support\Facades\DB;

final class StorePlayerService
{
    public function __construct(private HistoryPort $history) {}

    public function __invoke(string $name, string $alias): Player
    {
        return DB::transaction(function () use ($name, $alias): Player {
            $player = Player::create(['name' => $name, 'alias' => $alias]);

            // LOG-01
            $this->history->record(HistoryAction::PlayerCreated, $name, Change::between([], ['name' => $name, 'alias' => $alias]));

            return $player;
        });
    }
}
