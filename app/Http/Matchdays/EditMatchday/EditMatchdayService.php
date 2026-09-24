<?php

declare(strict_types=1);

namespace App\Http\Matchdays\EditMatchday;

use App\Domain\Shared\NameOrder;
use App\Models\Player;
use App\Models\Season;

final class EditMatchdayService
{
    /**
     * MD-01 — every player of the season, with the points entered so far.
     *
     * @return list<array{id: int, name: string, alias: string, points: int|null}>
     */
    public function __invoke(Season $season, int $matchday): array
    {
        $points = $season->scores()->where('matchday', $matchday)->pluck('points', 'player_id');

        return array_values($season->players()->get(['players.id', 'players.name', 'players.alias'])
            ->sort(fn (Player $a, Player $b): int => NameOrder::compare($a->name, $b->name))
            ->map(fn (Player $player): array => [
                'id' => $player->id,
                'name' => $player->name,
                'alias' => $player->alias,
                'points' => $points->has($player->id) ? (int) $points->get($player->id) : null,
            ])->all());
    }
}
