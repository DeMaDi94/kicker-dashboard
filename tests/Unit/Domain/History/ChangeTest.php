<?php

declare(strict_types=1);

use App\Domain\History\Change;
use App\Domain\History\ChangedField;
use App\Domain\History\HistoryAction;

describe('LOG-02 · each changed value with its old and its new value', function () {
    it('keeps only the values that differ, in the order given', function () {
        $changes = Change::between(
            ['penaltyStart' => 450, 'penaltyStep' => 50],
            ['penaltyStart' => 500, 'penaltyStep' => 50],
        );

        expect(array_map(fn (Change $change) => $change->toArray(), $changes))->toBe([
            ['field' => 'penaltyStart', 'subject' => null, 'old' => 450, 'new' => 500],
        ]);
    });

    it('reads a value missing before as one that did not exist', function () {
        $changes = Change::between([], ['name' => 'BK', 'alias' => 'bk_kicker']);

        expect(array_map(fn (Change $change) => [$change->field, $change->old, $change->new], $changes))->toBe([
            [ChangedField::Name, null, 'BK'],
            [ChangedField::Alias, null, 'bk_kicker'],
        ]);
    });

    it('records a removed value as gone', function () {
        expect(Change::between(['settlementMatchday' => 17], ['settlementMatchday' => null])[0]->toArray())
            ->toBe(['field' => 'settlementMatchday', 'subject' => null, 'old' => 17, 'new' => null]);
    });

    it('names each player whose points were entered or changed, by name A–Z (D3), and no one else', function () {
        $changes = Change::points(
            [1 => 'Schwabe', 2 => 'BK', 3 => 'FK'],
            [1 => 62, 2 => 40, 3 => 55],
            [1 => 65, 2 => 40, 3 => 58],
        );

        expect(array_map(fn (Change $change) => $change->toArray(), $changes))->toBe([
            ['field' => 'points', 'subject' => 'FK', 'old' => 55, 'new' => 58],
            ['field' => 'points', 'subject' => 'Schwabe', 'old' => 62, 'new' => 65],
        ]);
    });

    it('shows points entered for the first time with no old value', function () {
        expect(Change::points([7 => 'BK'], [], [7 => 62])[0]->toArray())
            ->toBe(['field' => 'points', 'subject' => 'BK', 'old' => null, 'new' => 62]);
    });

    it('lists a season’s players before and after, by name A–Z (D3)', function () {
        expect(Change::players(['FK', 'BK'], ['JLS', 'BK'])[0]->toArray())
            ->toBe(['field' => 'players', 'subject' => null, 'old' => 'BK, FK', 'new' => 'BK, JLS']);
    });

    it('sees no change when the same players take part in another order', function () {
        expect(Change::players(['FK', 'BK'], ['BK', 'FK']))->toBe([]);
    });
});

describe('LOG-01 · only changes are recorded', function () {
    it('skips a save that changed nothing', function (HistoryAction $action) {
        expect($action->isRecorded([]))->toBeFalse();
    })->with(array_values(array_filter(
        HistoryAction::cases(),
        fn (HistoryAction $action) => ! in_array($action, [HistoryAction::SeasonDeleted, HistoryAction::SeasonRestored], true),
    )));

    it('records deleting and restoring a season, which change no value', function (HistoryAction $action) {
        expect($action->isRecorded([]))->toBeTrue();
    })->with([HistoryAction::SeasonDeleted, HistoryAction::SeasonRestored]);

    it('records any action that changed a value', function () {
        expect(HistoryAction::PointsSaved->isRecorded([new Change(ChangedField::Points, 1, 2, 'BK')]))->toBeTrue();
    });
});
