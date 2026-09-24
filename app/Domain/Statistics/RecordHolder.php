<?php

declare(strict_types=1);

namespace App\Domain\Statistics;

/**
 * Who holds a record, and where (STAT-10). The closest and the most
 * expensive matchday (STAT-15) have no player; the season records have no
 * matchday.
 */
final readonly class RecordHolder
{
    public function __construct(
        public int $seasonId,
        public string $seasonName,
        public ?int $playerId = null,
        public ?string $playerName = null,
        public ?int $matchday = null,
        public ?string $playerAlias = null,
    ) {}
}
