<?php

declare(strict_types=1);

use Symfony\Component\Finder\Finder;

/*
 * Area boundaries in the HTTP layer.
 *
 * A service may depend on services in its own area only. What another area
 * offers, it offers through a Port in its own Ports/ folder — see
 * .claude/rules/architecture.md. Everything behind that Port is private.
 *
 * These are written as filesystem scans rather than Pest arch() expectations:
 * the areas are not known in advance (they come from docs/GLOSSARY.md), and a
 * scan holds the rule from the first area folder onwards, whatever it is
 * called. The starter kit's own Controllers, Middleware and Requests folders
 * are scaffolding and exempt.
 */

const STARTER_KIT_FOLDERS = ['Controllers', 'Middleware', 'Requests'];

function httpLayerPath(): string
{
    // These tests deliberately do not boot Laravel, so no base_path() here.
    return dirname(__DIR__, 2).'/app/Http';
}

/**
 * Every `use App\Http\…` in the HTTP layer, as [file, area, importedFqn].
 *
 * @return list<array{string, string, string}>
 */
function httpLayerImports(): array
{
    $base = httpLayerPath();

    if (! is_dir($base)) {
        return [];
    }

    $imports = [];

    foreach (Finder::create()->files()->in($base)->name('*.php') as $file) {
        $relative = str_replace(DIRECTORY_SEPARATOR, '/', $file->getRelativePathname());
        $area = explode('/', $relative)[0];

        // The starter kit's scaffolding is exempt (docs/DECISIONS.md B2).
        if (in_array($area, STARTER_KIT_FOLDERS, true)) {
            continue;
        }

        preg_match_all('/^use (App\\\\Http\\\\[^; ]+)/m', (string) $file->getContents(), $matches);

        foreach ($matches[1] as $fqn) {
            $imports[] = [$relative, $area, $fqn];
        }
    }

    return $imports;
}

it('does not let one area import another area past its Ports folder', function () {
    $violations = [];

    foreach (httpLayerImports() as [$file, $area, $fqn]) {
        $segments = explode('\\', $fqn);
        $importedArea = $segments[2] ?? '';

        if ($importedArea === '' || $importedArea === $area) {
            continue;
        }

        if (in_array($importedArea, STARTER_KIT_FOLDERS, true)) {
            continue;
        }

        if (($segments[3] ?? '') !== 'Ports') {
            $violations[] = "{$file} imports {$fqn}";
        }
    }

    expect($violations)->toBe([]);
});

it('does not let a Port reach into an area other than its own', function () {
    $violations = [];

    foreach (httpLayerImports() as [$file, $area, $fqn]) {
        if (! str_contains($file, '/Ports/')) {
            continue;
        }

        $importedArea = explode('\\', $fqn)[2] ?? '';

        if ($importedArea !== '' && $importedArea !== $area) {
            $violations[] = "{$file} delegates outside its area: {$fqn}";
        }
    }

    expect($violations)->toBe([]);
});

it('keeps Ports thin: a Port delegates and does not talk to the database', function () {
    $base = httpLayerPath();
    $violations = [];

    if (is_dir($base)) {
        foreach (Finder::create()->files()->in($base)->path('Ports')->name('*Port.php') as $file) {
            $contents = (string) $file->getContents();

            foreach (['Illuminate\Support\Facades\DB', 'Illuminate\Http\Request', 'Inertia\Inertia'] as $forbidden) {
                if (str_contains($contents, "use {$forbidden};")) {
                    $violations[] = "{$file->getRelativePathname()} uses {$forbidden}";
                }
            }
        }
    }

    expect($violations)->toBe([]);
});

/**
 * Every PHP file of an area's actions whose name ends in $suffix, as [relative path, contents].
 *
 * @return list<array{string, string}>
 */
function areaFiles(string $suffix): array
{
    $base = httpLayerPath();

    if (! is_dir($base)) {
        return [];
    }

    $files = [];

    foreach (Finder::create()->files()->in($base)->name("*{$suffix}.php") as $file) {
        $relative = str_replace(DIRECTORY_SEPARATOR, '/', $file->getRelativePathname());

        if (in_array(explode('/', $relative)[0], STARTER_KIT_FOLDERS, true)) {
            continue;
        }

        $files[] = [$relative, (string) $file->getContents()];
    }

    return $files;
}

it('keeps controllers to the HTTP edge: invokable, and no database', function () {
    $violations = [];

    foreach (areaFiles('Controller') as [$file, $contents]) {
        if (! str_contains($contents, 'function __invoke(')) {
            $violations[] = "{$file} is not invokable";
        }

        foreach (['Illuminate\Support\Facades\DB', 'Illuminate\Database\\'] as $forbidden) {
            if (str_contains($contents, "use {$forbidden}")) {
                $violations[] = "{$file} uses {$forbidden} — that is the service's job";
            }
        }
    }

    expect($violations)->toBe([]);
});

it('keeps services final, strict, invokable and free of HTTP', function () {
    $violations = [];

    foreach (areaFiles('Service') as [$file, $contents]) {
        if (! str_contains($contents, 'declare(strict_types=1);')) {
            $violations[] = "{$file} does not declare strict types";
        }

        if (! preg_match('/^final (readonly )?class /m', $contents)) {
            $violations[] = "{$file} is not final";
        }

        if (! str_contains($contents, 'function __invoke(')) {
            $violations[] = "{$file} is not invokable";
        }

        foreach (['Illuminate\Http\Request', 'Inertia\Inertia'] as $forbidden) {
            if (str_contains($contents, "use {$forbidden};")) {
                $violations[] = "{$file} uses {$forbidden} — a service takes typed input, not a request";
            }
        }
    }

    expect($violations)->toBe([]);
});
