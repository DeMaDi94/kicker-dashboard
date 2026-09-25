<?php

declare(strict_types=1);

namespace App\Http\History\ShowHistory;

use App\Domain\History\ChangedField;
use App\Domain\History\HistoryAction;
use App\Models\HistoryEntry;

/**
 * LOG-02, LOG-03 — the history, newest entry first.
 *
 * @phpstan-type ChangeLine array{field: value-of<ChangedField>, subject: string|null, old: int|string|null, new: int|string|null}
 * @phpstan-type EntryLine array{id: int, recordedAt: string, userName: string|null, action: value-of<HistoryAction>, subject: string|null, matchday: int|null, changes: list<ChangeLine>}
 */
final class ShowHistoryService
{
    /** D17 — twenty entries per page, as the user list (B16). */
    public const PAGE_SIZE = 20;

    /**
     * @return array{entries: list<EntryLine>, pagination: array{page: int, lastPage: int, total: int}}
     */
    public function __invoke(int $page): array
    {
        $entries = HistoryEntry::query()
            ->with('user:id,name,deleted_at')
            ->orderByDesc('recorded_at')
            ->orderByDesc('id')
            ->paginate(self::PAGE_SIZE, page: $page);

        return [
            'entries' => array_values(array_map(fn (HistoryEntry $entry): array => [
                'id' => $entry->id,
                // LOG-03 — the app's time zone is Europe/Berlin; the offset travels along.
                'recordedAt' => $entry->recorded_at->toIso8601String(),
                'userName' => $entry->user?->name,
                'action' => $entry->action->value,
                'subject' => $entry->subject,
                'matchday' => $entry->matchday,
                'changes' => $entry->changes,
            ], $entries->items())),
            'pagination' => [
                'page' => $entries->currentPage(),
                'lastPage' => $entries->lastPage(),
                'total' => $entries->total(),
            ],
        ];
    }
}
