<?php

declare(strict_types=1);

use App\Domain\Penalties\PenaltyScale;
use App\Domain\Statistics\CareerStats;
use App\Domain\Statistics\FormGrade;
use App\Domain\Statistics\HeadToHead;
use App\Domain\Statistics\LeagueRecords;
use App\Domain\Statistics\MatchdayLine;
use App\Domain\Statistics\PenaltyBox;
use App\Domain\Statistics\PlayerSeasonStats;
use App\Domain\Statistics\RecordHolder;
use App\Domain\Statistics\SeasonTimeline;

/*
 * Worked by hand, scale 3,00 € / 1,00 €:
 *   MD1  Anna 50  Bert 40  Cleo 30   places 1 2 3   penalties 1 2 3 €
 *   MD2  Anna 20  Bert 60  Cleo 60   places 2 1 1   penalties 3 2 2 €
 *   MD3  Anna 70  Bert 10  Cleo 40   places 1 3 2   penalties 1 3 2 €
 *   MD4  Anna 99 only — incomplete, ignored (STAT-02)
 *   Overall after MD1: A B C · after MD2: B C A · after MD3: A C B
 *   Into the box: MD1 6 €, MD2 7 €, MD3 6 € — balance 6, 13, 19 €
 */
function statsSeason(?int $settlement = null): SeasonTimeline
{
    return new SeasonTimeline(
        [1 => 'Anna', 2 => 'Bert', 3 => 'Cleo'],
        [
            4 => [1 => 99],
            1 => [1 => 50, 2 => 40, 3 => 30],
            2 => [1 => 20, 2 => 60, 3 => 60],
            3 => [1 => 70, 2 => 10, 3 => 40],
        ],
        new PenaltyScale(300, 100),
        $settlement,
    );
}

describe('STAT-02 · only complete matchdays count', function () {
    it('keeps the complete matchdays, in order', function () {
        expect(array_map(fn ($matchday) => $matchday->number, statsSeason()->matchdays))->toBe([1, 2, 3]);
    });
});

describe('STAT-03 · key figures', function () {
    it('gives total, average, best and worst, place and penalties', function () {
        $anna = PlayerSeasonStats::of(1, statsSeason());

        expect([$anna->matchdaysPlayed, $anna->totalPoints, $anna->averagePoints])->toBe([3, 140, 46.7])
            ->and([$anna->bestPoints, $anna->bestMatchdays, $anna->worstPoints, $anna->worstMatchdays])->toBe([70, [3], 20, [2]])
            ->and([$anna->place, $anna->penaltyCents])->toBe([1, 500]);
    });

    it('splits the penalties as PEN-04 does', function () {
        $anna = PlayerSeasonStats::of(1, statsSeason(settlement: 1));

        expect([$anna->firstHalfPenaltyCents, $anna->secondHalfPenaltyCents])->toBe([100, 400]);
    });

    it('has no average before a matchday is complete', function () {
        $stats = PlayerSeasonStats::of(1, new SeasonTimeline([1 => 'Anna'], [], new PenaltyScale(300, 100)));

        expect([$stats->averagePoints, $stats->bestPoints, $stats->lines])->toBe([null, null, []]);
    });
});

// STAT-04, STAT-05, STAT-06 — the lines behind the three graphs.
it('lines up points, league average, places and penalties per matchday (STAT-04, STAT-05, STAT-06)', function () {
    $lines = array_map(fn (MatchdayLine $line): array => [
        $line->matchday, $line->points, $line->leagueAverage, $line->dayPlace, $line->overallPlace, $line->penaltyCents, $line->cumulativePenaltyCents,
    ], PlayerSeasonStats::of(1, statsSeason())->lines);

    expect($lines)->toBe([
        [1, 50, 40.0, 1, 1, 100, 100],
        [2, 20, 46.7, 2, 3, 300, 400],
        [3, 70, 40.0, 1, 1, 100, 500],
    ]);
});

describe('STAT-07 · achievements and streaks', function () {
    it('counts wins and „Rote Laterne“, a tie counting for each', function () {
        $anna = PlayerSeasonStats::of(1, statsSeason());
        $cleo = PlayerSeasonStats::of(3, statsSeason());

        expect([$anna->wins, $anna->lanterns])->toBe([2, 1])
            ->and([$cleo->wins, $cleo->lanterns])->toBe([1, 1]);
    });

    it('counts a tie for last place as „Rote Laterne“ for each', function () {
        $season = new SeasonTimeline([1 => 'Anna', 2 => 'Bert', 3 => 'Cleo'], [1 => [1 => 50, 2 => 10, 3 => 10]], new PenaltyScale(300, 100));

        expect(array_map(fn (int $id): array => [PlayerSeasonStats::of($id, $season)->wins, PlayerSeasonStats::of($id, $season)->lanterns], [1, 2, 3]))
            ->toBe([[1, 0], [0, 1], [0, 1]]);
    });

    it('counts penalty-free matchdays and the longest run of them', function () {
        $season = new SeasonTimeline(
            [1 => 'Anna', 2 => 'Bert', 3 => 'Cleo'],
            [
                1 => [1 => 90, 2 => 20, 3 => 10],
                2 => [1 => 90, 2 => 20, 3 => 10],
                3 => [1 => 5, 2 => 20, 3 => 10],
                4 => [1 => 90, 2 => 20, 3 => 10],
            ],
            new PenaltyScale(200, 100),
        );

        $anna = PlayerSeasonStats::of(1, $season);

        expect([$anna->penaltyFreeMatchdays, $anna->longestPenaltyFreeStreak])->toBe([3, 2]);
    });

    it('grades the form of the last five by the third of the day’s places', function () {
        $form = PlayerSeasonStats::of(1, statsSeason())->form;

        expect(array_map(fn (array $day): array => [$day['matchday'], $day['place'], $day['grade']], $form))
            ->toBe([[1, 1, FormGrade::Good], [2, 2, FormGrade::Bad], [3, 1, FormGrade::Good]]);
    });

    it('rounds the thirds up (D11)', function () {
        expect(array_map(fn (int $place): FormGrade => FormGrade::of($place, 13), [5, 6, 8, 9]))
            ->toBe([FormGrade::Good, FormGrade::Middle, FormGrade::Middle, FormGrade::Bad])
            ->and(FormGrade::of(1, 1))->toBe(FormGrade::Good);
    });

    it('keeps only the last five matchdays in the form', function () {
        $points = [];
        foreach (range(1, 7) as $matchday) {
            $points[$matchday] = [1 => $matchday, 2 => 50];
        }

        $form = PlayerSeasonStats::of(1, new SeasonTimeline([1 => 'Anna', 2 => 'Bert'], $points, new PenaltyScale(100, 50)))->form;

        expect(array_column($form, 'matchday'))->toBe([3, 4, 5, 6, 7]);
    });
});

describe('STAT-08 · the all-time balance', function () {
    it('adds up one player’s seasons and averages the places of those with a table', function () {
        $anna = PlayerSeasonStats::of(1, statsSeason());
        $empty = PlayerSeasonStats::of(1, new SeasonTimeline([1 => 'Anna'], [], new PenaltyScale(300, 100)));

        $career = CareerStats::of([$anna, $anna, $empty]);

        expect([$career->seasonsPlayed, $career->averagePlace, $career->totalPoints, $career->totalPenaltyCents, $career->totalWins])
            ->toBe([3, 1.0, 280, 1000, 4]);
    });
});

it('counts a level matchday in the head-to-head (STAT-09)', function () {
    $season = new SeasonTimeline([1 => 'Anna', 2 => 'Bert'], [1 => [1 => 30, 2 => 30], 2 => [1 => 40, 2 => 20]], new PenaltyScale(100, 50));

    $duel = HeadToHead::of(1, 2, $season);

    expect([$duel->aAhead, $duel->level, $duel->bAhead])->toBe([1, 1, 0]);
});

it('compares two players matchday by matchday (STAT-09)', function () {
    $duel = HeadToHead::of(1, 2, statsSeason());

    expect([$duel->aAhead, $duel->level, $duel->bAhead])->toBe([2, 0, 1])
        ->and($duel->lines[1])->toBe(['matchday' => 2, 'a' => 20, 'b' => 60]);
});

describe('STAT-10 · league records', function () {
    it('finds each record and every holder of it', function () {
        $records = LeagueRecords::of([7 => ['name' => '2026/27', 'timeline' => statsSeason()]]);
        $who = fn (?object $record): array => array_map(
            fn (RecordHolder $holder): array => [$holder->playerName, $holder->matchday],
            $record->holders ?? [],
        );

        expect([$records->highestScore?->value, $who($records->highestScore)])->toBe([70, [['Anna', 3]]])
            ->and([$records->lowestScore?->value, $who($records->lowestScore)])->toBe([10, [['Bert', 3]]])
            ->and([$records->mostWins?->value, $who($records->mostWins)])->toBe([2, [['Anna', null]]])
            ->and([$records->highestPenalty?->value, $who($records->highestPenalty)])->toBe([700, [['Cleo', null], ['Bert', null]]])
            ->and([$records->closestMatchday?->value, $who($records->closestMatchday)])->toBe([20, [[null, 1]]]);
    });

    it('finds the most expensive matchday, every one of a tie, without a player (STAT-15)', function () {
        $records = LeagueRecords::of([
            7 => ['name' => '2026/27', 'timeline' => statsSeason()],
            8 => ['name' => '2027/28', 'timeline' => statsSeason()],
        ]);

        expect($records->mostExpensiveMatchday?->value)->toBe(700)
            ->and(array_map(
                fn (RecordHolder $holder): array => [$holder->seasonName, $holder->playerId, $holder->matchday],
                $records->mostExpensiveMatchday->holders ?? [],
            ))->toBe([['2026/27', null, 2], ['2027/28', null, 2]]);
    });

    // D11 — tied holders follow the overall table, whatever order the points came in.
    it('lists tied holders in the order of the overall table, with their alias (D4)', function () {
        $season = new SeasonTimeline(
            [1 => 'Anna', 2 => 'Bert', 3 => 'Cleo'],
            [1 => [1 => 90, 2 => 90, 3 => 10], 2 => [1 => 0, 2 => 50, 3 => 10]],
            new PenaltyScale(300, 100),
            aliases: [1 => 'anna_k', 2 => 'bert_k', 3 => 'cleo_k'],
        );

        $holders = LeagueRecords::of([7 => ['name' => '2026/27', 'timeline' => $season]])->highestScore->holders ?? [];

        expect(array_map(fn (RecordHolder $holder): array => [$holder->playerName, $holder->playerAlias], $holders))
            ->toBe([['Bert', 'bert_k'], ['Anna', 'anna_k']]);
    });

    it('holds no record before a matchday is complete', function () {
        $records = LeagueRecords::of([7 => ['name' => '2026/27', 'timeline' => new SeasonTimeline([1 => 'Anna'], [], new PenaltyScale(1, 1))]]);

        expect([$records->highestScore, $records->mostWins, $records->highestPenalty, $records->mostExpensiveMatchday])->toBe([null, null, null, null]);
    });
});

it('names each matchday’s winners, „Rote Laterne“ and average (STAT-11)', function () {
    $second = statsSeason()->matchdays[1];

    expect([$second->winners(), $second->lanterns(), round($second->average(), 1)])->toBe([[2, 3], [1], 46.7]);
});

it('sums the money that went into the box on each matchday (STAT-13)', function () {
    expect(array_map(fn ($matchday): int => $matchday->penaltyTotal(), statsSeason()->matchdays))->toBe([600, 700, 600]);
});

describe('STAT-12 · the penalty box', function () {
    it('sums what went in and lists who paid most first, then the name', function () {
        $box = PenaltyBox::of(statsSeason(settlement: 1));

        expect([$box->totalCents, $box->firstHalfCents, $box->secondHalfCents])->toBe([1900, 600, 1300])
            ->and(array_map(fn (array $payer): array => [$payer['name'], $payer['penaltyCents']], $box->payers))
            ->toBe([['Bert', 700], ['Cleo', 700], ['Anna', 500]]);
    });

    it('has no halves without a settlement', function () {
        $box = PenaltyBox::of(statsSeason());

        expect([$box->firstHalfCents, $box->secondHalfCents])->toBe([null, null]);
    });
});
