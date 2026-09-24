<?php

declare(strict_types=1);

namespace App\Http\Statistics\ComparePlayers;

use App\Domain\Shared\NameOrder;
use App\Domain\Statistics\HeadToHead;
use App\Http\Statistics\Shared\SeasonTimelines;
use App\Models\Season;

/**
 * STAT-09 — two players of a season side by side. A player who is not in the
 * season is not chosen.
 *
 * @phpstan-type ComparePage array{
 *     season: array{id: int, name: string},
 *     players: list<array{id: int, name: string, alias: string}>,
 *     a: int|null,
 *     b: int|null,
 *     duel: HeadToHead|null
 * }
 */
final class ComparePlayersService
{
    public function __construct(private SeasonTimelines $timelines) {}

    /**
     * @return ComparePage
     */
    public function __invoke(Season $season, ?int $a, ?int $b): array
    {
        $timeline = $this->timelines->forSeason($season);
        $participants = $timeline->participants;

        $a = isset($participants[$a ?? 0]) ? $a : null;
        $b = isset($participants[$b ?? 0]) ? $b : null;

        $players = [];
        foreach ($participants as $id => $name) {
            $players[] = ['id' => $id, 'name' => $name, 'alias' => $timeline->aliasOf($id)];
        }
        usort($players, fn (array $x, array $y): int => NameOrder::compare($x['name'], $y['name']));

        return [
            'season' => ['id' => $season->id, 'name' => $season->name],
            'players' => $players,
            'a' => $a,
            'b' => $b,
            'duel' => $a !== null && $b !== null && $a !== $b ? HeadToHead::of($a, $b, $timeline) : null,
        ];
    }
}
