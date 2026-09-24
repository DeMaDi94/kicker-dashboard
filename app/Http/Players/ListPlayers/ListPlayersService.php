<?php

declare(strict_types=1);

namespace App\Http\Players\ListPlayers;

use App\Domain\Shared\NameOrder;
use App\Models\Player;

/**
 * Every player, by name A–Z (D3).
 */
final class ListPlayersService
{
    /**
     * @return list<array{id: int, name: string, alias: string}>
     */
    public function __invoke(): array
    {
        return array_values(Player::query()->get(['id', 'name', 'alias'])
            ->sort(fn (Player $a, Player $b): int => NameOrder::compare($a->name, $b->name))
            ->map(fn (Player $player): array => ['id' => $player->id, 'name' => $player->name, 'alias' => $player->alias])->all());
    }
}
