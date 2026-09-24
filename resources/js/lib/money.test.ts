import { describe, expect, it } from 'vitest';
import { formatCents } from './money';

describe('PEN-02 · amounts in euros', () => {
    it('writes cents as the example writes them', () => {
        expect(formatCents(450, 'de')).toBe('4,50 €');
        expect(formatCents(0, 'de')).toBe('0,00 €');
    });
});
