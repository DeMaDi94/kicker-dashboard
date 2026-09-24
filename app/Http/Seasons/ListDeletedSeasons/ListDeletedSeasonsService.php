<?php

declare(strict_types=1);

namespace App\Http\Seasons\ListDeletedSeasons;

use App\Models\Season;

/**
 * SEA-06 — the deleted seasons an admin can restore, in the order the season
 * choice uses (ACC-01): the one created last first.
 */
final class ListDeletedSeasonsService
{
    /**
     * @return list<array{id: int, name: string}>
     */
    public function __invoke(): array
    {
        return array_values(Season::onlyTrashed()->latest('id')->get(['id', 'name'])
            ->map(fn (Season $season): array => ['id' => $season->id, 'name' => $season->name])->all());
    }
}
