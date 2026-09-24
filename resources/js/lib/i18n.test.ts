import { describe, expect, it } from 'vitest';
import { translate } from '@/lib/i18n';

describe('translate', () => {
    const catalogue = {
        Save: 'Speichern',
        'Welcome, :name': 'Willkommen, :name',
        ':count files': ':count Dateien',
    };

    it('looks the key up in the catalogue', () => {
        expect(translate(catalogue, 'Save')).toBe('Speichern');
    });

    it('falls back to the key, which is the English text', () => {
        expect(translate(catalogue, 'Cancel')).toBe('Cancel');
    });

    it('fills placeholders, numbers included', () => {
        expect(translate(catalogue, 'Welcome, :name', { name: 'Ada' })).toBe(
            'Willkommen, Ada',
        );
        expect(translate(catalogue, ':count files', { count: 3 })).toBe(
            '3 Dateien',
        );
    });

    it('capitalises for :Name and upper-cases for :NAME, as __() does', () => {
        expect(translate({}, ':Name / :NAME / :name', { name: 'ada' })).toBe(
            'Ada / ADA / ada',
        );
    });

    it('prefers the longest placeholder', () => {
        expect(
            translate({}, ':name and :names', { name: 'one', names: 'many' }),
        ).toBe('one and many');
    });

    it('leaves a placeholder it was given no value for', () => {
        expect(translate({}, 'Hello :name')).toBe('Hello :name');
    });
});
