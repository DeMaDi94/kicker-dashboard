<?php

declare(strict_types=1);

namespace App\Spec;

use RuntimeException;

/**
 * Reads the requirement files (SpecFiles::requirementSources()) and hands back the requirements
 * they declare, in file order.
 *
 * Several files are one catalogue: an id may be declared once, and an area belongs to one file.
 *
 * The Markdown is the single source of truth — there is no database table and
 * no generated copy that could drift from it. Everything else in the harness
 * (the status ledger, the coverage report, the Stop gate) is derived from what
 * this class parses.
 */
final class RequirementCatalogue
{
    /** `## 3. Invoices (`INV`)` */
    private const AREA_PATTERN = '/^##\s+\d+\.\s+(?<name>.+?)\s+\(`(?<prefix>[A-Z]{2,4})`\)\s*$/';

    /** `- **INV-04** — Invoice numbers are gapless per calendar year: …` */
    private const REQUIREMENT_PATTERN = '/^-\s+\*\*(?<id>[A-Z]{2,4}-\d{2})\*\*\s+—\s*(?<text>.*)$/u';

    /** @var list<Requirement>|null */
    private ?array $requirements = null;

    /** @var list<string> */
    private readonly array $paths;

    public function __construct(string $path, string ...$more)
    {
        $this->paths = [$path, ...array_values($more)];
    }

    public static function default(): self
    {
        return new self(...SpecFiles::requirementSources());
    }

    /** @return list<Requirement> */
    public function all(): array
    {
        return $this->requirements ??= $this->parseAll();
    }

    /** @return list<string> */
    public function ids(): array
    {
        return array_map(fn (Requirement $requirement) => $requirement->id, $this->all());
    }

    public function has(string $id): bool
    {
        return in_array($id, $this->ids(), true);
    }

    /**
     * Requirements grouped by area prefix, in the order the document declares.
     *
     * @return array<string, list<Requirement>>
     */
    public function byArea(): array
    {
        $areas = [];

        foreach ($this->all() as $requirement) {
            $areas[$requirement->areaPrefix][] = $requirement;
        }

        return $areas;
    }

    /** @return array<string, string> prefix => area name */
    public function areaNames(): array
    {
        $names = [];

        foreach ($this->all() as $requirement) {
            $names[$requirement->areaPrefix] = $requirement->areaName;
        }

        return $names;
    }

    /** @return list<Requirement> */
    private function parseAll(): array
    {
        $requirements = [];
        $declaredIn = [];
        $areaFile = [];

        foreach ($this->paths as $path) {
            foreach ($this->parse($path) as $requirement) {
                if (isset($declaredIn[$requirement->id])) {
                    throw new RuntimeException(sprintf(
                        '%s is declared twice (%s, %s).',
                        $requirement->id,
                        $declaredIn[$requirement->id],
                        $path,
                    ));
                }

                $owner = $areaFile[$requirement->areaPrefix] ??= $path;

                if ($owner !== $path) {
                    throw new RuntimeException(sprintf(
                        'Area %s is declared in both %s and %s.',
                        $requirement->areaPrefix,
                        $owner,
                        $path,
                    ));
                }

                $declaredIn[$requirement->id] = $path;
                $requirements[] = $requirement;
            }
        }

        return $requirements;
    }

    /** @return list<Requirement> */
    private function parse(string $path): array
    {
        if (! is_file($path)) {
            throw new RuntimeException("Requirements catalogue not found at {$path}.");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES);

        if ($lines === false) {
            throw new RuntimeException("Could not read {$path}.");
        }

        $requirements = [];
        $areaPrefix = '';
        $areaName = '';
        $openIndex = null;

        foreach ($lines as $line) {
            if (preg_match(self::AREA_PATTERN, $line, $area) === 1) {
                $areaPrefix = $area['prefix'];
                $areaName = $area['name'];
                $openIndex = null;

                continue;
            }

            if (preg_match(self::REQUIREMENT_PATTERN, $line, $found) === 1) {
                $requirements[] = new Requirement(
                    id: $found['id'],
                    areaPrefix: $areaPrefix,
                    areaName: $areaName,
                    text: $found['text'],
                );
                $openIndex = count($requirements) - 1;

                continue;
            }

            // A requirement's text wraps over indented continuation lines.
            if ($openIndex !== null && preg_match('/^\s{2,}\S/', $line) === 1) {
                $requirements[$openIndex] = new Requirement(
                    id: $requirements[$openIndex]->id,
                    areaPrefix: $requirements[$openIndex]->areaPrefix,
                    areaName: $requirements[$openIndex]->areaName,
                    text: $requirements[$openIndex]->text."\n".trim($line),
                );

                continue;
            }

            $openIndex = null;
        }

        // Rebuilding entries in place (the continuation-line branch) loses the list shape.
        return array_values($requirements);
    }
}
