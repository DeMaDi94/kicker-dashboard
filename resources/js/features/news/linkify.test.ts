import { describe, expect, it } from 'vitest';
import { linkify } from './linkify';

describe('NEWS-01 · links in a news post are clickable', () => {
    it('leaves text without an address as it is', () => {
        expect(linkify('Kasse am Freitag.\nBitte bar.')).toEqual([
            { kind: 'text', value: 'Kasse am Freitag.\nBitte bar.' },
        ]);
    });

    it('cuts out every web address', () => {
        expect(
            linkify('Tabelle: https://example.org/a?b=1 und http://x.de'),
        ).toEqual([
            { kind: 'text', value: 'Tabelle: ' },
            { kind: 'link', value: 'https://example.org/a?b=1' },
            { kind: 'text', value: ' und ' },
            { kind: 'link', value: 'http://x.de' },
        ]);
    });

    it('keeps a sentence’s closing punctuation out of the link', () => {
        expect(linkify('Siehe https://example.org/x.')).toEqual([
            { kind: 'text', value: 'Siehe ' },
            { kind: 'link', value: 'https://example.org/x' },
            { kind: 'text', value: '.' },
        ]);
    });

    it('does not link an address without http or https', () => {
        expect(linkify('javascript:alert(1) www.example.org')).toEqual([
            { kind: 'text', value: 'javascript:alert(1) www.example.org' },
        ]);
    });
});
