<?php

declare(strict_types=1);

namespace App\Http\Players\Ports;

use App\Http\Players\ListPlayers\ListPlayersService;

/**
 * What the Players area lets others ask: every player, by name A–Z (D3) —
 * the choice an admin picks a season's players from (SEA-02).
 */
final class PlayerListPort
{
    public function __construct(private ListPlayersService $list) {}

    /**
     * @return list<array{id: int, name: string, alias: string}>
     */
    public function all(): array
    {
        return ($this->list)();
    }
}
