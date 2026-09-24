<?php

declare(strict_types=1);

namespace App\Domain\Statistics;

/**
 * STAT-10 — one record: its value and everyone who holds it.
 */
final readonly class LeagueRecord
{
    /**
     * @param  list<RecordHolder>  $holders
     */
    public function __construct(
        public int $value,
        public array $holders,
    ) {}
}
