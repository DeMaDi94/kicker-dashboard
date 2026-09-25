<?php

declare(strict_types=1);

namespace App\Http\History\Ports;

use App\Domain\History\Change;
use App\Domain\History\HistoryAction;
use App\Http\History\RecordEntry\RecordEntryService;

/**
 * What the History area lets others do: record a change to the league's data
 * (LOG-01). Called inside the caller's transaction, so an entry exists
 * exactly when the change does.
 */
final class HistoryPort
{
    public function __construct(private RecordEntryService $record) {}

    /**
     * @param  string|null  $subject  the season's or the player's name, as it is after the change
     * @param  list<Change>  $changes
     */
    public function record(HistoryAction $action, ?string $subject, array $changes, ?int $matchday = null): void
    {
        ($this->record)($action, $subject, $changes, $matchday);
    }
}
