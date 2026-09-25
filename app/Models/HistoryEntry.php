<?php

namespace App\Models;

use App\Domain\History\ChangedField;
use App\Domain\History\HistoryAction;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * LOG-01, LOG-02 — one change to the league's data: who, when, which action,
 * on which season or player (its name as it was), and each changed value.
 * LOG-03 — never pruned.
 *
 * @property int $id
 * @property int|null $user_id
 * @property HistoryAction $action
 * @property string|null $subject
 * @property int|null $matchday
 * @property list<array{field: value-of<ChangedField>, subject: string|null, old: int|string|null, new: int|string|null}> $changes
 * @property Carbon $recorded_at
 * @property-read User|null $user
 */
#[Fillable(['user_id', 'action', 'subject', 'matchday', 'changes', 'recorded_at'])]
class HistoryEntry extends Model
{
    public $timestamps = false;

    /**
     * B15 — a deleted user's entries still name them.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'action' => HistoryAction::class,
            'matchday' => 'integer',
            'changes' => 'array',
            'recorded_at' => 'datetime',
        ];
    }
}
