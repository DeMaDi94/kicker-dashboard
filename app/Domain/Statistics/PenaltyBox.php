<?php

declare(strict_types=1);

namespace App\Domain\Statistics;

use App\Domain\Shared\NameOrder;
use App\Domain\Standings\StandingRow;

/**
 * STAT-12 — the season's penalty box: what went in, split as PEN-04 splits
 * it, and who paid most first (then the name A–Z, D3).
 */
final readonly class PenaltyBox
{
    /**
     * @param  list<array{playerId: int, name: string, alias: string, penaltyCents: int}>  $payers
     */
    public function __construct(
        public int $totalCents,
        public ?int $firstHalfCents,
        public ?int $secondHalfCents,
        public array $payers,
    ) {}

    public static function of(SeasonTimeline $season): self
    {
        $rows = $season->standings;
        $sum = fn (callable $pick): int => array_sum(array_map($pick, $rows));
        $split = $season->settlementMatchday !== null;

        usort($rows, fn (StandingRow $a, StandingRow $b): int => $b->penaltyCents <=> $a->penaltyCents
            ?: NameOrder::compare($a->name, $b->name));

        return new self(
            $sum(fn (StandingRow $row): int => $row->penaltyCents),
            $split ? $sum(fn (StandingRow $row): int => $row->firstHalfPenaltyCents ?? 0) : null,
            $split ? $sum(fn (StandingRow $row): int => $row->secondHalfPenaltyCents ?? 0) : null,
            array_map(fn (StandingRow $row): array => [
                'playerId' => $row->playerId,
                'name' => $row->name,
                'alias' => $season->aliasOf($row->playerId),
                'penaltyCents' => $row->penaltyCents,
            ], $rows),
        );
    }
}
