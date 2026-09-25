<?php

declare(strict_types=1);

namespace App\Http\History\RecordEntry;

use App\Domain\History\Change;
use App\Domain\History\HistoryAction;
use App\Models\HistoryEntry;
use Illuminate\Contracts\Auth\Factory as Auth;

final class RecordEntryService
{
    public function __construct(private Auth $auth) {}

    /**
     * LOG-01, LOG-02 — the signed-in user, the time, the action and every
     * changed value. The acting user is read here rather than passed along by
     * every caller: each change the history holds is made by the one signed in.
     *
     * @param  list<Change>  $changes
     */
    public function __invoke(HistoryAction $action, ?string $subject, array $changes, ?int $matchday = null): void
    {
        if (! $action->isRecorded($changes)) {
            return;
        }

        $userId = $this->auth->guard()->id();

        HistoryEntry::create([
            'user_id' => is_int($userId) ? $userId : null,
            'action' => $action,
            'subject' => $subject,
            'matchday' => $matchday,
            'changes' => array_map(fn (Change $change): array => $change->toArray(), $changes),
            'recorded_at' => now(),
        ]);
    }
}
