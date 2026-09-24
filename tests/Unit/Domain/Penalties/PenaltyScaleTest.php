<?php

declare(strict_types=1);

use App\Domain\Penalties\PenaltyScale;

describe('PEN-01 · the penalty scale', function () {
    it('charges the start amount to the lowest score and one step less per higher score', function () {
        $scale = new PenaltyScale(450, 50);

        expect($scale->penalties([1 => 10, 2 => 20, 3 => 30]))->toBe([1 => 450, 2 => 400, 3 => 350]);
    });

    it('counts places, not players: equal points pay the same', function () {
        $scale = new PenaltyScale(300, 100);

        expect($scale->penalties([1 => 5, 2 => 5, 3 => 9]))->toBe([1 => 300, 2 => 300, 3 => 200]);
    });

    it('never charges less than 0 €', function () {
        $scale = new PenaltyScale(100, 50);

        expect($scale->penalties([1 => 1, 2 => 2, 3 => 3, 4 => 4]))->toBe([1 => 100, 2 => 50, 3 => 0, 4 => 0]);
    });

    it('refuses a negative amount', function () {
        new PenaltyScale(-1, 50);
    })->throws(InvalidArgumentException::class);
});

// PEN-02 — the product owner's worked example, verbatim.
it('matches the PEN-02 example', function () {
    $points = [1 => 40, 2 => 40, 3 => 55, 4 => 60, 5 => 60, 6 => 72, 7 => 80];

    expect((new PenaltyScale(450, 50))->penalties($points))
        ->toBe([1 => 450, 2 => 450, 3 => 400, 4 => 350, 5 => 350, 6 => 300, 7 => 250]);
});

/*
 * PEN-01 — the league's own sheet (tests/Fixtures/kicker-26-27-penalties.json).
 * It holds penalties, not points; any points in the order the penalties imply
 * must produce the same penalties, 27,50 € per matchday.
 */
it('reproduces the league sheet of 2026/27', function (array $case) {
    /** @var array<string, int> $expected */
    $expected = $case['penaltyCents'];
    arsort($expected);

    // The highest penalty scored lowest: rank the players, then give each a score.
    $points = [];
    $score = 0;
    foreach (array_keys($expected) as $player) {
        $points[$player] = $score += 10;
    }

    $penalties = (new PenaltyScale($case['startCents'], $case['stepCents']))->penalties($points);

    expect($penalties)->toEqualCanonicalizing($case['penaltyCents'])
        ->and(array_sum($penalties))->toBe($case['totalCents']);
})->with(goldenVectors('kicker-26-27-penalties'));
