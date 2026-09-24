<?php

declare(strict_types=1);

// spec-coverage: ignore — the ids below are parser fixtures, not implemented requirements.

use App\Spec\RequirementCatalogue;
use App\Spec\SpecFiles;

function specFixture(string $markdown): string
{
    $path = sys_get_temp_dir().'/spec-spec-'.uniqid().'.md';
    file_put_contents($path, $markdown);

    return $path;
}

it('parses as many requirements as the catalogue says it holds', function () {
    $path = SpecFiles::requirements();
    $catalogue = new RequirementCatalogue($path);

    // The document states its own total in its header table. If the parser and the document
    // disagree, one of them is wrong and it matters which — this is the only assertion that
    // catches a silently skipped area or a requirement written in the wrong format.
    preg_match('/^\|\s*Requirements\s*\|\s*(\d+)/m', (string) file_get_contents($path), $stated);

    expect($stated)->toHaveKey(1)
        ->and($catalogue->all())->toHaveCount((int) $stated[1]);
});

it('ignores the indented format example in the catalogue template', function () {
    $path = specFixture(<<<'MD'
        ## Format

            ## 3. Invoices (`INV`)

            - **INV-01** — Invoice numbers follow `RE-YYYY-NNNN`,
              zero-padded to four digits.
        MD);

    expect((new RequirementCatalogue($path))->all())->toBe([]);

    unlink($path);
});

it('parses a synthetic catalogue without touching the real one', function () {
    $path = sys_get_temp_dir().'/spec-spec-'.uniqid().'.md';
    file_put_contents($path, <<<'MD'
        ## 1. Made up area (`ZZZ`)

        Some prose that is not a requirement.

        - **ZZZ-01** — First rule, which wraps
          onto a second line.
        - **ZZZ-02** — Second rule.

        ## 2. Another area (`YY`)

        - **YY-01** — Third rule.
        MD);

    $catalogue = new RequirementCatalogue($path);

    expect($catalogue->ids())->toBe(['ZZZ-01', 'ZZZ-02', 'YY-01'])
        ->and($catalogue->all()[0]->text)->toContain('onto a second line')
        ->and($catalogue->all()[2]->areaName)->toBe('Another area')
        ->and($catalogue->has('ZZZ-01'))->toBeTrue()
        ->and($catalogue->has('ZZZ-99'))->toBeFalse();

    unlink($path);
});

it('fails loudly when the catalogue is missing', function () {
    (new RequirementCatalogue('/nonexistent/REQUIREMENTS.md'))->all();
})->throws(RuntimeException::class);

it('reads several files as one catalogue, in file order', function () {
    $first = specFixture("## 1. First (`ZZZ`)\n\n- **ZZZ-01** — One.\n");
    $second = specFixture("## 1. Second (`YY`)\n\n- **YY-01** — Two.\n- **YY-02** — Three.\n");

    $catalogue = new RequirementCatalogue($first, $second);

    expect($catalogue->ids())->toBe(['ZZZ-01', 'YY-01', 'YY-02'])
        ->and($catalogue->areaNames())->toBe(['ZZZ' => 'First', 'YY' => 'Second']);

    unlink($first);
    unlink($second);
});

it('refuses an id declared in two files', function () {
    $first = specFixture("## 1. First (`ZZZ`)\n\n- **ZZZ-01** — One.\n");
    $second = specFixture("## 1. Second (`YY`)\n\n- **ZZZ-01** — Again.\n");

    try {
        expect(fn () => (new RequirementCatalogue($first, $second))->all())
            ->toThrow(RuntimeException::class, 'ZZZ-01 is declared twice');
    } finally {
        unlink($first);
        unlink($second);
    }
});

it('refuses an area prefix that two files declare', function () {
    $first = specFixture("## 1. First (`ZZZ`)\n\n- **ZZZ-01** — One.\n");
    $second = specFixture("## 1. More of it (`ZZZ`)\n\n- **ZZZ-02** — Two.\n");

    try {
        expect(fn () => (new RequirementCatalogue($first, $second))->all())
            ->toThrow(RuntimeException::class, 'Area ZZZ is declared in both');
    } finally {
        unlink($first);
        unlink($second);
    }
});
