import { describe, expect, it } from 'vitest';
import { formatDate, formatDateTime } from './date';

describe('LOG-03 · times in German time (Europe/Berlin)', () => {
    it('shows a UTC instant at its Berlin wall-clock time', () => {
        expect(formatDateTime('2026-09-25T12:05:00+00:00', 'de')).toBe(
            '25.09.2026, 14:05',
        );
    });

    it('takes the Berlin calendar day, not the UTC one', () => {
        expect(formatDate('2026-09-25T22:30:00+00:00', 'de')).toBe(
            '26.09.2026',
        );
    });
});
