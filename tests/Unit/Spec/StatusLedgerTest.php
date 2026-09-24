<?php

declare(strict_types=1);

// spec-coverage: ignore — the ids below are ledger fixtures, not implemented requirements.

use App\Spec\RequirementStatus;
use App\Spec\StatusLedger;

function ledgerPath(string $contents = ''): string
{
    $path = sys_get_temp_dir().'/spec-ledger-'.uniqid().'.txt';

    if ($contents !== '') {
        file_put_contents($path, $contents);
    }

    return $path;
}

it('reads an entry with and without a reason', function () {
    $ledger = new StatusLedger(ledgerPath(<<<'TXT'
        # a comment, and a blank line follows

        AAA-01  done
        AAA-02  wont-do    Because the server replaces it.
        TXT));

    expect($ledger->statusFor('AAA-01'))->toBe(RequirementStatus::Done)
        ->and($ledger->reasonFor('AAA-01'))->toBe('')
        ->and($ledger->statusFor('AAA-02'))->toBe(RequirementStatus::WontDo)
        ->and($ledger->reasonFor('AAA-02'))->toBe('Because the server replaces it.')
        ->and($ledger->statusFor('AAA-03'))->toBeNull();
});

it('treats a missing file as an empty ledger rather than an error', function () {
    expect((new StatusLedger(ledgerPath()))->ids())->toBe([]);
});

it('rejects an unknown status instead of silently defaulting', function () {
    new StatusLedger(ledgerPath("AAA-01  nearly-done\n"));
})->throws(RuntimeException::class, 'unknown status');

it('rejects a line that has no status', function () {
    new StatusLedger(ledgerPath("AAA-01\n"));
})->throws(RuntimeException::class);

it('adds only the ids it does not already know', function () {
    $ledger = new StatusLedger(ledgerPath("AAA-01  done\n"));

    $added = $ledger->addMissing(['AAA-01', 'AAA-02', 'AAA-03']);

    expect($added)->toBe(['AAA-02', 'AAA-03'])
        ->and($ledger->statusFor('AAA-01'))->toBe(RequirementStatus::Done)
        ->and($ledger->statusFor('AAA-02'))->toBe(RequirementStatus::Planned);
});

it('round-trips through a write, preserving statuses and reasons', function () {
    $path = ledgerPath("AAA-01  changed  Inertia visit, not a reload.\n");
    $ledger = new StatusLedger($path);
    $ledger->set('AAA-02', RequirementStatus::InProgress);
    $ledger->write(['AAA-01', 'AAA-02']);

    $reread = new StatusLedger($path);

    expect($reread->statusFor('AAA-01'))->toBe(RequirementStatus::Changed)
        ->and($reread->reasonFor('AAA-01'))->toBe('Inertia visit, not a reload.')
        ->and($reread->statusFor('AAA-02'))->toBe(RequirementStatus::InProgress);

    unlink($path);
});

it('writes in the order the catalogue declares, not the order entries were added', function () {
    $path = ledgerPath();
    $ledger = new StatusLedger($path);
    $ledger->set('BBB-01', RequirementStatus::Done);
    $ledger->set('AAA-01', RequirementStatus::Done);
    $ledger->write(['AAA-01', 'BBB-01']);

    $written = array_values(array_filter(
        file($path, FILE_IGNORE_NEW_LINES) ?: [],
        fn (string $line) => $line !== '' && ! str_starts_with($line, '#'),
    ));

    expect($written[0])->toStartWith('AAA-01')
        ->and($written[1])->toStartWith('BBB-01');

    unlink($path);
});

describe('which statuses have to justify themselves', function () {
    it('demands a reason only for a deliberate divergence', function () {
        expect(RequirementStatus::Changed->requiresReason())->toBeTrue()
            ->and(RequirementStatus::WontDo->requiresReason())->toBeTrue()
            ->and(RequirementStatus::Done->requiresReason())->toBeFalse()
            ->and(RequirementStatus::Planned->requiresReason())->toBeFalse();
    });

    it('demands a test only from what claims to be finished', function () {
        expect(RequirementStatus::Done->requiresTest())->toBeTrue()
            ->and(RequirementStatus::InProgress->requiresTest())->toBeFalse()
            ->and(RequirementStatus::Changed->requiresTest())->toBeFalse();
    });
});
