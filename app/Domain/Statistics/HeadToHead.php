<?php

declare(strict_types=1);

namespace App\Domain\Statistics;

/**
 * STAT-09 — two players of one season side by side.
 */
final readonly class HeadToHead
{
    /**
     * @param  list<array{matchday: int, a: int, b: int}>  $lines
     */
    public function __construct(
        public array $lines,
        public int $aAhead,
        public int $level,
        public int $bAhead,
    ) {}

    public static function of(int $a, int $b, SeasonTimeline $season): self
    {
        $lines = [];
        $tally = ['a' => 0, 'level' => 0, 'b' => 0];

        foreach ($season->matchdays as $matchday) {
            $pa = $matchday->points[$a] ?? 0;
            $pb = $matchday->points[$b] ?? 0;
            $lines[] = ['matchday' => $matchday->number, 'a' => $pa, 'b' => $pb];
            $tally[match ($pa <=> $pb) {
                1 => 'a', 0 => 'level', -1 => 'b'
            }]++;
        }

        return new self($lines, $tally['a'], $tally['level'], $tally['b']);
    }
}
