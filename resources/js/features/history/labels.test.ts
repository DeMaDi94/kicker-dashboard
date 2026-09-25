import { describe, expect, it } from 'vitest';
import { NAV_ITEMS } from '@/layouts/shell/nav-items';
import { translate } from '@/lib/i18n';
import { changeLabel, changeValue } from './labels';

const t = (key: string, replacements?: Record<string, string | number>) =>
    translate({}, key, replacements);

describe('LOG-03 · „Verlauf“ in the navigation', () => {
    it('is the last entry, after „Besucher“, shown only with history.view', () => {
        const last = NAV_ITEMS[NAV_ITEMS.length - 1];

        expect(last?.title).toBe('History');
        expect(last?.permission).toBe('history.view');
        expect(last?.sections).toEqual(['/history']);
        expect(NAV_ITEMS[NAV_ITEMS.length - 2]?.title).toBe('Visitors');
    });
});

describe('LOG-02 · each changed value, old → new', () => {
    it('names the player whose points changed — „BK: 62 → 65“', () => {
        const change = {
            field: 'points',
            subject: 'BK',
            old: 62,
            new: 65,
        } as const;

        expect(changeLabel(change, t)).toBe('BK');
        expect(changeValue(change.field, change.old, t, 'de')).toBe('62');
        expect(changeValue(change.field, change.new, t, 'de')).toBe('65');
    });

    it('shows a value that was not there as a dash', () => {
        expect(changeValue('points', null, t, 'de')).toBe('—');
    });

    it('shows the penalty scale in euros', () => {
        expect(changeValue('penaltyStart', 450, t, 'de')).toMatch(/^4,50\s€$/);
    });
});
