<?php

declare(strict_types=1);

namespace App\Http\Statistics\ShowPlayer;

use App\Domain\Shared\NameOrder;
use App\Domain\Statistics\CareerStats;
use App\Domain\Statistics\PlayerSeasonStats;
use App\Http\Statistics\Shared\SeasonTimelines;
use App\Models\Player;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * STAT-01 — a player's page: the seasons they played, one season's
 * statistics (STAT-03–07) and the all-time balance (STAT-08).
 *
 * @phpstan-type PlayerPage array{
 *     player: array{id: int, name: string, alias: string},
 *     seasons: list<array{id: int, name: string}>,
 *     season: array{id: int, name: string, settlementMatchday: int|null}|null,
 *     stats: PlayerSeasonStats|null,
 *     career: CareerStats,
 *     opponents: list<array{id: int, name: string}>
 * }
 */
final class ShowPlayerService
{
    public function __construct(private SeasonTimelines $timelines) {}

    /**
     * @return PlayerPage
     */
    public function __invoke(Player $player, ?int $seasonId): array
    {
        $seasons = $this->timelines->forPlayer($player);

        // STAT-01 — without a choice, the season created last that the player plays in.
        $seasonId ??= array_key_first($seasons);

        if ($seasonId !== null && ! isset($seasons[$seasonId])) {
            throw new NotFoundHttpException;
        }

        $all = [];
        foreach ($seasons as $season) {
            $all[] = PlayerSeasonStats::of($player->id, $season['timeline']);
        }

        $chosen = $seasonId === null ? null : $seasons[$seasonId];
        $opponents = [];
        foreach ($chosen['timeline']->participants ?? [] as $id => $name) {
            if ($id !== $player->id) {
                $opponents[] = ['id' => $id, 'name' => $name];
            }
        }

        usort($opponents, fn (array $a, array $b): int => NameOrder::compare($a['name'], $b['name']));

        return [
            'player' => ['id' => $player->id, 'name' => $player->name, 'alias' => $player->alias],
            'seasons' => array_map(fn (int $id, array $season): array => ['id' => $id, 'name' => $season['name']], array_keys($seasons), $seasons),
            'season' => $chosen === null ? null : [
                'id' => $seasonId,
                'name' => $chosen['name'],
                'settlementMatchday' => $chosen['timeline']->settlementMatchday,
            ],
            'stats' => $chosen === null ? null : PlayerSeasonStats::of($player->id, $chosen['timeline']),
            'career' => CareerStats::of($all),
            'opponents' => $opponents,
        ];
    }
}
