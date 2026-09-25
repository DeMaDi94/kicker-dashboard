<?php

declare(strict_types=1);

namespace App\Domain\History;

/**
 * LOG-01 — every change to the league's data the history records. The stored
 * value is the wire format; the label is a translation key on the client.
 */
enum HistoryAction: string
{
    case PointsSaved = 'points.saved';
    case PlayerCreated = 'player.created';
    case PlayerUpdated = 'player.updated';
    case SeasonCreated = 'season.created';
    case SeasonDeleted = 'season.deleted';
    case SeasonRestored = 'season.restored';
    case SeasonPlayersChanged = 'season.players';
    case PenaltyScaleChanged = 'season.penalty-scale';
    case SettlementChanged = 'season.settlement';
    case NewsCreated = 'news.created';
    case NewsUpdated = 'news.updated';
    case NewsDeleted = 'news.deleted';

    /**
     * LOG-01 — the history holds changes: a save that changed no value is not
     * one. Deleting and restoring a season change no value, and are.
     *
     * @param  list<Change>  $changes
     */
    public function isRecorded(array $changes): bool
    {
        return $changes !== [] || in_array($this, [self::SeasonDeleted, self::SeasonRestored], true);
    }
}
