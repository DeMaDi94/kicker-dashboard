<?php

declare(strict_types=1);

use Symfony\Component\Finder\Finder;

/*
 * B6 — every translation key the code uses exists in every locale.
 *
 * Keys are the English source text, so English falls back to the key and
 * needs no entry; every other supported locale must have one, or its users
 * see English in the middle of their language and nothing reports it.
 *
 * Only literal keys can be checked — which is why `.claude/rules/i18n.md`
 * asks for literals. Laravel's group keys (`auth.failed`) live in
 * lang/{locale}/*.php and are the framework's to keep complete.
 *
 * Deliberately a scan rather than a booted test, so the Stop gate can run it
 * in well under a second.
 */

function repositoryRoot(): string
{
    return dirname(__DIR__, 2);
}

/**
 * @return list<string>
 */
function supportedLocales(): array
{
    /** @var array{locales: list<string>} $app */
    $app = require repositoryRoot().'/config/app.php';

    return $app['locales'];
}

/**
 * Every literal key passed to t() in resources/js and to __() in PHP, with the
 * first file that uses it.
 *
 * @return array<string, string>
 */
function translationKeysInUse(): array
{
    $root = repositoryRoot();
    $keys = [];

    $scans = [
        // `t('…')` / `i18nKey('…')` — not `.t(`, not `sort(`, not `format(`.
        [
            Finder::create()->files()->in($root.'/resources/js')->name(['*.ts', '*.tsx'])
                ->notName(['*.test.ts', '*.test.tsx', '*.d.ts'])
                ->exclude(['actions', 'routes', 'wayfinder']),
            '/(?<![\w.$])(?:t|i18nKey)\(\s*([\'"])((?:\\\\.|(?!\1).)*)\1/s',
        ],
        [
            Finder::create()->files()->in([$root.'/app', $root.'/resources/views', $root.'/routes'])->name('*.php'),
            '/(?<![\w>$])__\(\s*([\'"])((?:\\\\.|(?!\1).)*)\1/s',
        ],
    ];

    foreach ($scans as [$finder, $pattern]) {
        foreach ($finder as $file) {
            preg_match_all($pattern, (string) $file->getContents(), $matches);

            foreach ($matches[2] as $raw) {
                $key = stripcslashes($raw);

                if (preg_match('/^[\w-]+(\.[\w-]+)+$/', $key) === 1) {
                    continue;
                }

                $keys[$key] ??= str_replace($root.'/', '', $file->getPathname());
            }
        }
    }

    ksort($keys);

    return $keys;
}

/**
 * @return array<string, string>
 */
function catalogueFor(string $locale): array
{
    $path = repositoryRoot()."/lang/{$locale}.json";

    if (! is_file($path)) {
        return [];
    }

    /** @var array<string, string> $catalogue */
    $catalogue = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

    return $catalogue;
}

it('has an entry for every key the code uses, in every non-English locale', function (string $locale) {
    $catalogue = catalogueFor($locale);
    $missing = [];

    foreach (translationKeysInUse() as $key => $file) {
        if (! array_key_exists($key, $catalogue)) {
            $missing[] = "lang/{$locale}.json lacks \"{$key}\" (used in {$file})";
        }
    }

    expect($missing)->toBe([]);
})->with(fn (): array => array_values(array_diff(supportedLocales(), ['en'])));

it('has no empty translation', function (string $locale) {
    $empty = array_keys(array_filter(catalogueFor($locale), fn (string $line): bool => trim($line) === ''));

    expect($empty)->toBe([]);
})->with(fn (): array => supportedLocales());

it('finds the keys it is meant to find', function () {
    // A scanner that silently matches nothing would pass every check above.
    expect(translationKeysInUse())->toHaveKey('Profile updated.');
});
