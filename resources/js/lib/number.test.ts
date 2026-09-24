import { describe, expect, it } from 'vitest';
import { formatFixed, formatNumber, parseNumber } from './number';

describe('formatting a plain number in the active locale', () => {
    it('groups and drops unnecessary decimals', () => {
        expect(formatNumber(1234.5, 'de')).toBe('1.234,5');
        expect(formatNumber(1234.5, 'en')).toBe('1,234.5');
        expect(formatNumber(12000, 'de')).toBe('12.000');
        expect(formatNumber(12000, 'en')).toBe('12,000');
    });

    it('rounds to at most the given decimals', () => {
        expect(formatNumber(2.3456, 'de')).toBe('2,35');
        expect(formatNumber(2.3456, 'en', 3)).toBe('2.346');
    });

    it('keeps a fixed number of decimals where asked', () => {
        expect(formatFixed(0.8, 'de')).toBe('0,80');
        expect(formatFixed(0.8, 'en')).toBe('0.80');
        expect(formatFixed(1234, 'en', 1)).toBe('1,234.0');
    });
});

describe('parsing a plain number in the active locale', () => {
    it('reads the group and decimal separators of the locale', () => {
        expect(parseNumber('1.234,5', 'de')).toBe(1234.5);
        expect(parseNumber('1,234.5', 'en')).toBe(1234.5);
        expect(parseNumber('8', 'de')).toBe(8);
        expect(parseNumber('0,8', 'de')).toBe(0.8);
        expect(parseNumber('0.8', 'en')).toBe(0.8);
    });

    it('treats a leading minus as negative', () => {
        expect(parseNumber('-250,4', 'de')).toBe(-250.4);
        expect(parseNumber('-250.4', 'en')).toBe(-250.4);
    });

    it('reads back what it formats', () => {
        for (const locale of ['de', 'en']) {
            expect(parseNumber(formatNumber(-98765.43, locale), locale)).toBe(
                -98765.43,
            );
        }
    });

    it('returns null for input with no digit', () => {
        expect(parseNumber('abc', 'de')).toBeNull();
        expect(parseNumber('abc', 'en')).toBeNull();
        expect(parseNumber('-', 'en')).toBeNull();
        expect(parseNumber('', 'de')).toBeNull();
    });

    it('returns null when two decimal separators leave no single number', () => {
        expect(parseNumber('1,2,3', 'de')).toBeNull();
        expect(parseNumber('1.2.3', 'en')).toBeNull();
    });
});
