<?php

declare(strict_types=1);

// spec-coverage: ignore — the ids below are scanner fixtures, not implemented requirements.

use App\Spec\CitationScanner;

function scannerFixture(array $files): string
{
    $root = sys_get_temp_dir().'/spec-scan-'.uniqid();
    mkdir($root, 0o755, true);

    foreach ($files as $name => $contents) {
        $path = $root.'/'.$name;
        @mkdir(dirname($path), 0o755, true);
        file_put_contents($path, $contents);
    }

    return $root;
}

it('finds ids in Pest and Vitest files alike', function () {
    $root = scannerFixture([
        'Unit/SliderTest.php' => "<?php\ndescribe('AAA-09 · the slider', function () {});\n",
        'js/money.test.ts' => "// BBB-18\nit('formats', () => {});\n",
        'e2e/project.spec.ts' => "test('CCC-01 workflow', async () => {});\n",
    ]);

    $citations = (new CitationScanner([$root]))->citations();

    expect(array_keys($citations))->toBe(['AAA-09', 'BBB-18', 'CCC-01']);
});

it('ignores files that are not tests', function () {
    $root = scannerFixture([
        'Slider.php' => "<?php\n// AAA-09 lives here\n",
        'notes.md' => 'AAA-10 is discussed here',
        'Unit/SliderTest.php' => "<?php\n// AAA-11\n",
    ]);

    expect(array_keys((new CitationScanner([$root]))->citations()))->toBe(['AAA-11']);
});

it('records every file that cites the same requirement', function () {
    $root = scannerFixture([
        'Unit/MoneyTest.php' => "<?php\n// AAA-18\n",
        'js/money.test.ts' => "// AAA-18\n",
    ]);

    expect((new CitationScanner([$root]))->citations()['AAA-18'])->toHaveCount(2);
});

it('counts a requirement once however often a file names it', function () {
    $root = scannerFixture([
        'Unit/MoneyTest.php' => "<?php\n// AAA-18\n// AAA-18 again\n// and AAA-18\n",
    ]);

    expect((new CitationScanner([$root]))->citations()['AAA-18'])->toHaveCount(1);
});

it('honours the opt-out marker, so tests of the harness do not fake coverage', function () {
    $root = scannerFixture([
        'Unit/HarnessTest.php' => "<?php\n// spec-coverage: ignore\n// AAA-09\n",
        'Unit/RealTest.php' => "<?php\n// AAA-10\n",
    ]);

    expect(array_keys((new CitationScanner([$root]))->citations()))->toBe(['AAA-10']);
});

it('reads two-letter prefixes', function () {
    $root = scannerFixture(['Unit/ToastTest.php' => "<?php\n// ZZ-04\n"]);

    expect(array_keys((new CitationScanner([$root]))->citations()))->toBe(['ZZ-04']);
});

it('does not mistake an invoice number for a requirement id', function () {
    // A number like `RE-YYYY-NNNN`; a naive pattern reads the year as an id.
    $root = scannerFixture([
        'Unit/InvoiceTest.php' => "<?php\nexpect(\$number)->toBe('RE-2026-0001');\n",
    ]);

    expect((new CitationScanner([$root]))->citations())->toBe([]);
});

it('survives a path that does not exist', function () {
    expect((new CitationScanner(['/nonexistent/tests']))->citations())->toBe([]);
});
