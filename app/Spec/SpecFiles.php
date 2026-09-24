<?php

declare(strict_types=1);

namespace App\Spec;

/**
 * Where the requirement harness keeps its files.
 *
 * Resolved from this file's own location rather than through `base_path()`, so the catalogue,
 * the ledger and the scanner can all be exercised in a plain unit test without booting Laravel.
 * The tooling has no other reason to need the framework, and the tests are the faster for it.
 */
final class SpecFiles
{
    public static function root(): string
    {
        return dirname(__DIR__, 2);
    }

    public static function requirements(): string
    {
        return self::root().'/docs/REQUIREMENTS.md';
    }

    /**
     * Every requirement file, in the order the ledger lists their ids. One today; a project that
     * splits its catalogue (a second release, an appendix of additions) lists the files here.
     *
     * @return list<string>
     */
    public static function requirementSources(): array
    {
        return [self::requirements()];
    }

    public static function statusLedger(): string
    {
        return self::root().'/docs/spec/status.txt';
    }

    public static function coverageReport(): string
    {
        return self::root().'/docs/spec/COVERAGE.md';
    }

    /**
     * The trees scanned for requirement citations.
     *
     * @return list<string>
     */
    public static function testRoots(): array
    {
        return [
            self::root().'/tests',
            self::root().'/resources/js',
        ];
    }
}
