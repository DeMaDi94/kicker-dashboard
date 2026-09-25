<?php

declare(strict_types=1);

namespace App\Http\Players\UpdatePlayer;

use App\Domain\History\Change;
use App\Domain\History\HistoryAction;
use App\Http\History\Ports\HistoryPort;
use App\Models\Player;
use Illuminate\Support\Facades\DB;

final class UpdatePlayerService
{
    public function __construct(private HistoryPort $history) {}

    /**
     * PLY-02 — every view reads the name and the alias from the player, so the
     * change holds in every season at once; points, places and penalties hang
     * on the player's id and stay as they are.
     */
    public function __invoke(Player $player, string $name, string $alias): void
    {
        DB::transaction(function () use ($player, $name, $alias): void {
            $before = ['name' => $player->name, 'alias' => $player->alias];
            $player->update(['name' => $name, 'alias' => $alias]);

            // LOG-01
            $this->history->record(HistoryAction::PlayerUpdated, $name, Change::between($before, ['name' => $name, 'alias' => $alias]));
        });
    }
}
