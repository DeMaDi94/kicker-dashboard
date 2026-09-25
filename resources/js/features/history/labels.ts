import type { Translate } from '@/hooks/use-translation';
import { formatCents } from '@/lib/money';
import type { ChangeLine, ChangedField, HistoryAction } from './types';

/** LOG-02 — an entry's action; the stored value is never shown. */
export function actionLabel(action: HistoryAction, t: Translate): string {
    switch (action) {
        case 'points.saved':
            return t('Points saved');
        case 'player.created':
            return t('Player created');
        case 'player.updated':
            return t('Player changed');
        case 'season.created':
            return t('Season created');
        case 'season.deleted':
            return t('Season deleted');
        case 'season.restored':
            return t('Season restored');
        case 'season.players':
            return t('Players of the season changed');
        case 'season.penalty-scale':
            return t('Penalty scale changed');
        case 'season.settlement':
            return t('Interim settlement changed');
        case 'news.created':
            return t('News written');
        case 'news.updated':
            return t('News changed');
        case 'news.deleted':
            return t('News deleted');
    }
}

/** LOG-02 — whose or which value changed: the player for points, else the field. */
export function changeLabel(change: ChangeLine, t: Translate): string {
    switch (change.field) {
        case 'points':
            return change.subject ?? t('Points');
        case 'name':
            return t('Name');
        case 'alias':
            return t('Alias');
        case 'penaltyStart':
            return t('Start amount');
        case 'penaltyStep':
            return t('Step');
        case 'settlementMatchday':
            return t('Interim settlement');
        case 'players':
            return t('Players');
        case 'text':
            return t('News post');
    }
}

/** LOG-02 — one side of a change; `null` is a value that was not there. */
export function changeValue(
    field: ChangedField,
    value: number | string | null,
    t: Translate,
    locale: string,
): string {
    if (value === null) {
        return '—';
    }

    if (
        (field === 'penaltyStart' || field === 'penaltyStep') &&
        typeof value === 'number'
    ) {
        return formatCents(value, locale);
    }

    if (field === 'settlementMatchday') {
        return t('Matchday :number', { number: value });
    }

    return String(value);
}
