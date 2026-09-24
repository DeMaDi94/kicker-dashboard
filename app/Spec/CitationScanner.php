<?php

declare(strict_types=1);

namespace App\Spec;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Finds which tests cite which requirement.
 *
 * A test cites a requirement by naming its ID anywhere in the file — in the
 * describe/it description, a comment, or a data-set key. Textual matching is
 * deliberate: it works identically for Pest and Vitest, needs no plugin or
 * annotation API, and cannot fall out of step with either runner.
 */
final class CitationScanner
{
    private const ID_PATTERN = '/\b([A-Z]{2,4}-\d{2})\b/';

    /**
     * A file carrying this marker is skipped.
     *
     * Needed by the tests of this class and of the catalogue parser: they use requirement ids as
     * fixtures, which would otherwise register as coverage for rules nothing has implemented.
     */
    private const OPT_OUT = 'spec-coverage: ignore';

    /** @param list<string> $paths */
    public function __construct(private readonly array $paths) {}

    public static function default(): self
    {
        return new self(SpecFiles::testRoots());
    }

    /**
     * @return array<string, list<string>> requirement id => citing files, repo-relative
     */
    public function citations(): array
    {
        $citations = [];

        foreach ($this->testFiles() as $file) {
            $contents = file_get_contents($file);

            if ($contents === false || str_contains($contents, self::OPT_OUT)) {
                continue;
            }

            preg_match_all(self::ID_PATTERN, $contents, $found);

            foreach (array_unique($found[1]) as $id) {
                $citations[$id][] = $this->relative($file);
            }
        }

        ksort($citations);

        return $citations;
    }

    /** @return list<string> */
    private function testFiles(): array
    {
        $files = [];

        foreach ($this->paths as $path) {
            if (! is_dir($path)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

            /** @var SplFileInfo $file */
            foreach ($iterator as $file) {
                if ($file->isFile() && $this->isTest($file->getPathname())) {
                    $files[] = $file->getPathname();
                }
            }
        }

        sort($files);

        return $files;
    }

    private function isTest(string $path): bool
    {
        return (bool) preg_match('/(Test\.php|\.test\.tsx?|\.spec\.ts)$/', $path);
    }

    private function relative(string $path): string
    {
        return str_replace(SpecFiles::root().'/', '', $path);
    }
}
