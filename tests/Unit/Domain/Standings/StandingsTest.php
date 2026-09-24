<?php

declare(strict_types=1);

use App\Domain\Penalties\PenaltyScale;
use App\Domain\Standings\StandingRow;
use App\Domain\Standings\Standings;

/**
 * @param  list<StandingRow>  $rows
 * @return list<array{string, int, int, int}>
 */
function standingLines(array $rows): array
{
    return array_map(fn (StandingRow $row): array => [$row->name, $row->place, $row->points, $row->penaltyCents], $rows);
}

describe('STD-01 · the overall table', function () {
    it('orders by the sum of points, most first', function () {
        $rows = Standings::of([1 => 'B', 2 => 'A'], [1 => [1 => 60, 2 => 50], 2 => [1 => 40, 2 => 70]], new PenaltyScale(200, 100));

        expect(standingLines($rows))->toBe([['A', 1, 120, 300], ['B', 2, 100, 300]]);
    });

    it('counts only complete matchdays', function () {
        $rows = Standings::of([1 => 'A', 2 => 'B'], [1 => [1 => 50, 2 => 60], 2 => [1 => 99]], new PenaltyScale(200, 100));

        expect(standingLines($rows))->toBe([['B', 1, 60, 100], ['A', 2, 50, 200]]);
    });

    // D5 — equal sums share a place, and the next place follows densely.
    it('gives equal sums the same place, counted densely', function () {
        $rows = Standings::of([1 => 'A', 2 => 'B', 3 => 'C'], [1 => [1 => 50, 2 => 50, 3 => 10]], new PenaltyScale(200, 100));

        expect(array_map(fn (StandingRow $row): int => $row->place, $rows))->toBe([1, 1, 2]);
    });

    it('puts the lower penalty sum first within a place', function () {
        $matchdays = [
            1 => [1 => 20, 2 => 0, 3 => 100],
            2 => [1 => 20, 2 => 30, 3 => 100],
            3 => [1 => 20, 2 => 30, 3 => 100],
        ];

        $rows = Standings::of([1 => 'A', 2 => 'B', 3 => 'C'], $matchdays, new PenaltyScale(300, 100));

        expect(standingLines($rows))->toBe([['C', 1, 300, 300], ['B', 2, 60, 700], ['A', 2, 60, 800]]);
    });

    // D3 — equal points and equal penalties: the name A–Z.
    it('orders a full tie by name A–Z', function () {
        $matchdays = [1 => [1 => 10, 2 => 30, 3 => 30], 2 => [1 => 50, 2 => 30, 3 => 30]];

        $rows = Standings::of([1 => 'Zoe', 2 => 'Bert', 3 => 'Anna'], $matchdays, new PenaltyScale(300, 100));

        expect(standingLines($rows))->toBe([['Anna', 1, 60, 500], ['Bert', 1, 60, 500], ['Zoe', 1, 60, 500]]);
    });
});

// PEN-03 — the season's penalty sum per player.
it('sums each player\'s penalties over the season', function () {
    $rows = Standings::of([1 => 'A', 2 => 'B'], [1 => [1 => 10, 2 => 20], 2 => [1 => 10, 2 => 20]], new PenaltyScale(450, 50));

    expect(standingLines($rows))->toBe([['B', 1, 40, 800], ['A', 2, 20, 900]]);
});

describe('PEN-04 · the interim settlement', function () {
    $matchdays = [
        1 => [1 => 10, 2 => 20],
        2 => [1 => 10, 2 => 20],
        3 => [1 => 20, 2 => 10],
    ];

    it('splits the penalty sum at the settlement matchday', function () use ($matchdays) {
        $rows = Standings::of([1 => 'A', 2 => 'B'], $matchdays, new PenaltyScale(450, 50), 2);

        expect(array_map(fn (StandingRow $row): array => [$row->name, $row->firstHalfPenaltyCents, $row->secondHalfPenaltyCents, $row->penaltyCents], $rows))
            ->toBe([['B', 800, 450, 1250], ['A', 900, 400, 1300]]);
    });

    it('leaves points and places untouched', function () use ($matchdays) {
        $with = Standings::of([1 => 'A', 2 => 'B'], $matchdays, new PenaltyScale(450, 50), 2);
        $without = Standings::of([1 => 'A', 2 => 'B'], $matchdays, new PenaltyScale(450, 50));

        expect(array_map(fn (StandingRow $row): array => [$row->name, $row->place, $row->points], $with))
            ->toBe(array_map(fn (StandingRow $row): array => [$row->name, $row->place, $row->points], $without));
    });

    it('has no halves while no settlement is set', function () use ($matchdays) {
        $row = Standings::of([1 => 'A', 2 => 'B'], $matchdays, new PenaltyScale(450, 50))[0];

        expect($row->firstHalfPenaltyCents)->toBeNull()->and($row->secondHalfPenaltyCents)->toBeNull();
    });
});
