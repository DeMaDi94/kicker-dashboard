<?php

declare(strict_types=1);

use App\Domain\Matchdays\MatchdayResult;
use App\Domain\Matchdays\MatchdayRow;
use App\Domain\Penalties\PenaltyScale;

describe('MD-02 · a matchday result', function () {
    it('shows no place or penalty before the matchday is complete', function () {
        $rows = MatchdayResult::of([1 => 'Paul', 2 => 'Anna'], [1 => 40], new PenaltyScale(450, 50));

        expect(array_map(fn (MatchdayRow $row): array => [$row->name, $row->points, $row->place, $row->penaltyCents], $rows))
            ->toBe([['Anna', null, null, null], ['Paul', 40, null, null]]);
    });

    // MD-03, PEN-01 — once complete, by place; D3 — equal places by name.
    it('ranks a complete matchday and charges its penalties', function () {
        $rows = MatchdayResult::of([1 => 'Paul', 2 => 'Anna', 3 => 'Ömer'], [1 => 40, 2 => 60, 3 => 40], new PenaltyScale(450, 50));

        expect(array_map(fn (MatchdayRow $row): array => [$row->name, $row->place, $row->penaltyCents], $rows))
            ->toBe([['Anna', 1, 400], ['Ömer', 2, 450], ['Paul', 2, 450]]);
    });
});
