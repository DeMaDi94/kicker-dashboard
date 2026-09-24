<?php

declare(strict_types=1);

use App\Domain\Matchdays\MatchdayPlaces;

describe('MD-03 · places of a matchday', function () {
    it('gives more points the better place', function () {
        expect(MatchdayPlaces::of([1 => 50, 2 => 80, 3 => 65]))->toBe([1 => 3, 2 => 1, 3 => 2]);
    });

    it('gives equal points the same place and counts densely: 1, 2, 3, 3, 4', function () {
        expect(MatchdayPlaces::of([1 => 90, 2 => 80, 3 => 70, 4 => 70, 5 => 60]))
            ->toBe([1 => 1, 2 => 2, 3 => 3, 4 => 3, 5 => 4]);
    });

    // MD-01 — points may be negative.
    it('ranks negative points below zero', function () {
        expect(MatchdayPlaces::of([1 => -5, 2 => 0]))->toBe([1 => 2, 2 => 1]);
    });
});

describe('MD-02 · a complete matchday', function () {
    it('is complete once every player has points', function () {
        expect(MatchdayPlaces::isComplete([1, 2], [1 => 10, 2 => 0]))->toBeTrue();
    });

    it('is not complete while a player lacks points', function () {
        expect(MatchdayPlaces::isComplete([1, 2], [1 => 10]))->toBeFalse();
    });

    it('is not complete without players', function () {
        expect(MatchdayPlaces::isComplete([], []))->toBeFalse();
    });
});
