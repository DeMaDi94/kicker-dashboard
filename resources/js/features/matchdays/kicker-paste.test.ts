import { describe, expect, it } from 'vitest';
import leaguePage from '../../../../tests/Fixtures/kicker-matchday-paste.txt?raw';
import { matchKickerRows, readMatchdayRanking } from './kicker-paste';

describe('MD-05 · reading the matchday ranking from a copied kicker page', () => {
    it('reads the „Spieltagswertung“ of the whole copied page, not the season ranking', () => {
        expect(readMatchdayRanking(leaguePage)).toEqual([
            { name: 'MR93', points: 85 },
            { name: 'Cherryoke', points: 77 },
            { name: 'schwabe', points: 67 },
            { name: 'DennisDi(Admin)', points: 64 },
            { name: 'Niklas', points: 58 },
            { name: 'Eric Dornscheidt', points: 58 },
            { name: 'Philipp-Benjamin Leh', points: 56 },
            { name: 'Oppi', points: 51 },
            { name: 'Jan Lukas Lo', points: 43 },
            { name: 'Hakan', points: 41 },
            { name: 'Axel19191', points: 35 },
            { name: 'Speedy', points: 30 },
            { name: 'BK', points: 26 },
        ]);
    });

    it('reads rows copied on one line each, and negative points', () => {
        const text = [
            'Spieltagswertung',
            'Platz\tName\tPunkte',
            '1\tAnna\t12',
            '–\tBert\t12',
            '3\tCarl\t-4',
            '4\tDora\t−7',
            'Saisonwertung',
            '1\tAnna\t99',
        ].join('\n');

        expect(readMatchdayRanking(text)).toEqual([
            { name: 'Anna', points: 12 },
            { name: 'Bert', points: 12 },
            { name: 'Carl', points: -4 },
            { name: 'Dora', points: -7 },
        ]);
    });

    it('finds nothing in a text without a matchday ranking', () => {
        expect(readMatchdayRanking('Saisonwertung\n1\tAnna\t99')).toBeNull();
        expect(
            readMatchdayRanking('Spieltagswertung\nPlatz\tName\tPunkte'),
        ).toBeNull();
        expect(readMatchdayRanking('')).toBeNull();
    });
});

describe('MD-05 · matching the ranking to the players by alias', () => {
    const rows = readMatchdayRanking(leaguePage) ?? [];

    it('gives each player the points of their alias (PLY-01), the admin marker ignored', () => {
        const match = matchKickerRows(rows, [
            { id: 1, name: 'MR', alias: 'MR93' },
            { id: 2, name: 'DD', alias: 'DennisDi' },
            { id: 3, name: 'ED', alias: 'Eric Dornscheidt' },
        ]);

        expect([...match.points]).toEqual([
            [1, 85],
            [2, 64],
            [3, 58],
        ]);
        expect(match.missing).toEqual([]);
    });

    it('compares the alias without surrounding blanks and in any case (D12)', () => {
        const match = matchKickerRows(rows, [
            { id: 1, name: 'MR', alias: ' mr93 ' },
            { id: 2, name: 'SW', alias: 'Schwabe' },
        ]);

        expect([...match.points]).toEqual([
            [1, 85],
            [2, 67],
        ]);
    });

    it('names the players without points and the names without a player', () => {
        const match = matchKickerRows(
            [
                { name: 'MR93', points: 85 },
                { name: 'Stranger', points: 10 },
            ],
            [
                { id: 1, name: 'MR', alias: 'MR93' },
                { id: 2, name: 'BK', alias: 'bk_kicker' },
            ],
        );

        expect([...match.points]).toEqual([[1, 85]]);
        expect(match.missing).toEqual([{ id: 2, name: 'BK' }]);
        expect(match.unknown).toEqual(['Stranger']);
    });
});
