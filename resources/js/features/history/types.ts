/** LOG-01 — mirrors App\Domain\History\HistoryAction. */
export type HistoryAction =
    | 'points.saved'
    | 'player.created'
    | 'player.updated'
    | 'season.created'
    | 'season.deleted'
    | 'season.restored'
    | 'season.players'
    | 'season.penalty-scale'
    | 'season.settlement'
    | 'news.created'
    | 'news.updated'
    | 'news.deleted';

/** LOG-02 — mirrors App\Domain\History\ChangedField. */
export type ChangedField =
    | 'name'
    | 'alias'
    | 'points'
    | 'penaltyStart'
    | 'penaltyStep'
    | 'settlementMatchday'
    | 'players'
    | 'text';

export type ChangeLine = {
    field: ChangedField;
    subject: string | null;
    old: number | string | null;
    new: number | string | null;
};

/** Mirrors ShowHistoryService's EntryLine. */
export type HistoryEntry = {
    id: number;
    recordedAt: string;
    userName: string | null;
    action: HistoryAction;
    subject: string | null;
    matchday: number | null;
    changes: ChangeLine[];
};
