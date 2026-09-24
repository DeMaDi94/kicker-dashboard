<?php

declare(strict_types=1);

namespace App\Spec;

use RuntimeException;

/**
 * The per-requirement status ledger, `docs/spec/status.txt`.
 *
 * Deliberately a flat text file rather than YAML or JSON: hundreds of
 * hand-maintained rows want to grep cleanly, diff one line per change, and be editable without
 * quoting rules. Format is `ID  status  optional reason`, `#` starts a comment.
 */
final class StatusLedger
{
    /** @var array<string, array{status: RequirementStatus, reason: string}> */
    private array $entries = [];

    public function __construct(private readonly string $path)
    {
        if (is_file($this->path)) {
            $this->read();
        }
    }

    public static function default(): self
    {
        return new self(SpecFiles::statusLedger());
    }

    public function statusFor(string $id): ?RequirementStatus
    {
        return $this->entries[$id]['status'] ?? null;
    }

    public function reasonFor(string $id): string
    {
        return $this->entries[$id]['reason'] ?? '';
    }

    /** @return list<string> */
    public function ids(): array
    {
        return array_keys($this->entries);
    }

    public function set(string $id, RequirementStatus $status, string $reason = ''): void
    {
        $this->entries[$id] = ['status' => $status, 'reason' => $reason];
    }

    /**
     * Add any requirement the ledger does not know yet as `planned`.
     *
     * @param  list<string>  $ids
     * @return list<string> the ids that were added
     */
    public function addMissing(array $ids): array
    {
        $added = [];

        foreach ($ids as $id) {
            if (! isset($this->entries[$id])) {
                $this->entries[$id] = ['status' => RequirementStatus::Planned, 'reason' => ''];
                $added[] = $id;
            }
        }

        return $added;
    }

    /** @param list<string> $orderedIds */
    public function write(array $orderedIds): void
    {
        $lines = [
            '# Implementation status, one line per requirement in docs/REQUIREMENTS.md.',
            '#',
            '# Format:  <ID>  <status>  [reason]',
            '# Status:  '.implode(' | ', array_map(
                fn (RequirementStatus $status) => $status->value,
                RequirementStatus::cases(),
            )),
            '#',
            '# `changed` and `wont-do` must carry a reason — without one a reviewer reading',
            '# REQUIREMENTS.md a year from now reads them as a missing feature.',
            '# Regenerate the skeleton with `php artisan spec:index`; edit statuses by hand.',
            '',
        ];

        $width = max(array_map(mb_strlen(...), $orderedIds) ?: [8]);

        foreach ($orderedIds as $id) {
            $entry = $this->entries[$id] ?? ['status' => RequirementStatus::Planned, 'reason' => ''];
            $line = str_pad($id, $width + 2).str_pad($entry['status']->value, 13);
            $lines[] = rtrim($line.$entry['reason']);
        }

        if (! is_dir(dirname($this->path))) {
            mkdir(dirname($this->path), 0o755, true);
        }

        file_put_contents($this->path, implode("\n", $lines)."\n");
    }

    private function read(): void
    {
        $lines = file($this->path, FILE_IGNORE_NEW_LINES);

        if ($lines === false) {
            throw new RuntimeException("Could not read {$this->path}.");
        }

        foreach ($lines as $number => $line) {
            $trimmed = trim($line);

            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }

            $parts = preg_split('/\s+/u', $trimmed, 3) ?: [];

            if (count($parts) < 2) {
                throw new RuntimeException(
                    sprintf('%s line %d: expected "<ID> <status> [reason]".', $this->path, $number + 1),
                );
            }

            $status = RequirementStatus::tryFrom($parts[1]);

            if ($status === null) {
                throw new RuntimeException(
                    sprintf('%s line %d: unknown status "%s".', $this->path, $number + 1, $parts[1]),
                );
            }

            $this->entries[$parts[0]] = ['status' => $status, 'reason' => trim($parts[2] ?? '')];
        }
    }
}
