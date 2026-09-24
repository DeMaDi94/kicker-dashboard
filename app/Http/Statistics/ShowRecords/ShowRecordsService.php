<?php

declare(strict_types=1);

namespace App\Http\Statistics\ShowRecords;

use App\Domain\Statistics\LeagueRecords;
use App\Http\Statistics\Shared\SeasonTimelines;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * STAT-10 — the league's records, over all seasons or the one chosen.
 *
 * @phpstan-type RecordsPage array{
 *     seasons: list<array{id: int, name: string}>,
 *     season: int|null,
 *     records: LeagueRecords
 * }
 */
final class ShowRecordsService
{
    public function __construct(private SeasonTimelines $timelines) {}

    /**
     * @return RecordsPage
     */
    public function __invoke(?int $seasonId): array
    {
        $seasons = $this->timelines->all();

        if ($seasonId !== null && ! isset($seasons[$seasonId])) {
            throw new NotFoundHttpException;
        }

        return [
            'seasons' => array_map(fn (int $id, array $season): array => ['id' => $id, 'name' => $season['name']], array_keys($seasons), $seasons),
            'season' => $seasonId,
            'records' => LeagueRecords::of($seasonId === null ? $seasons : [$seasonId => $seasons[$seasonId]]),
        ];
    }
}
