import { fireEvent, render, screen, waitFor } from '@testing-library/react';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { translate } from '@/lib/i18n';
import type { MatchdayBlock } from './types';

const toast = vi.fn();
vi.mock('@/components/core/toast', () => ({ toast }));

const { matchdayShareText, ShareMatchdayButton } =
    await import('./share-matchday');

/* The German wording MD-06 quotes, as lang/de.json holds it. */
const german = {
    ':app :season · Matchday :number': ':app :season · Spieltag :number',
    'Ø :average points · :amount into the box':
        'Ø :average Punkte · :amount in die Kasse',
};
const t = (key: string, replacements?: Record<string, string | number>) =>
    translate(german, key, replacements);

const row = (
    playerId: number,
    name: string,
    points: number | null,
    place: number | null,
    penaltyCents: number | null,
) => ({
    playerId,
    name,
    alias: name.toLowerCase(),
    points,
    place,
    penaltyCents,
});

const complete: MatchdayBlock = {
    number: 7,
    complete: true,
    hasPoints: true,
    rows: [
        row(1, 'BK', 84, 1, 0),
        row(2, 'TR', 84, 1, 0),
        row(3, 'JLS', 33, 2, 450),
        row(4, 'FK', 29, 3, 500),
    ],
    highlights: {
        winners: [{ playerId: 1, name: 'BK', alias: 'bk' }],
        lanterns: [{ playerId: 4, name: 'FK', alias: 'fk' }],
        average: 57.3,
        penaltyCents: 2750,
    },
};

const text = (matchday: MatchdayBlock) =>
    matchdayShareText({
        app: 'Vivalaraza',
        seasonName: '2026/27',
        matchday,
        url: 'https://example.test/seasons/3?matchday=7',
        t,
        locale: 'de',
    })?.replace(/ /g, ' ');

describe('MD-06 · the shared text of a matchday', () => {
    it('holds the heading, every player in the matchday order, the average and the box, then the link', () => {
        expect(text(complete)).toBe(
            [
                'Vivalaraza 2026/27 · Spieltag 7',
                '',
                '1. BK 84 · 0,00 €',
                '1. TR 84 · 0,00 €',
                '2. JLS 33 · 4,50 €',
                '3. FK 29 · 5,00 €',
                '',
                'Ø 57,3 Punkte · 27,50 € in die Kasse',
                '',
                'https://example.test/seasons/3?matchday=7',
            ].join('\n'),
        );
    });

    it('is not offered until every player has points (MD-02)', () => {
        expect(
            text({
                ...complete,
                complete: false,
                rows: [
                    row(1, 'BK', 84, null, null),
                    row(4, 'FK', null, null, null),
                ],
                highlights: null,
            }),
        ).toBeUndefined();
    });
});

describe('MD-06 · „Teilen“ hands the text on (D18)', () => {
    const writeText = vi.fn<(text: string) => Promise<void>>();

    beforeEach(() => {
        toast.mockClear();
        writeText.mockReset().mockResolvedValue(undefined);
        Object.defineProperty(navigator, 'clipboard', {
            value: { writeText },
            configurable: true,
        });
    });

    afterEach(() => {
        Reflect.deleteProperty(navigator, 'share');
    });

    const withShare = (share: (data: ShareData) => Promise<void>) =>
        Object.defineProperty(navigator, 'share', {
            value: share,
            configurable: true,
        });

    it('opens the device share menu with the text', async () => {
        const share = vi.fn<(data: ShareData) => Promise<void>>();
        share.mockResolvedValue(undefined);
        withShare(share);
        render(<ShareMatchdayButton text="Spieltag 7" />);

        fireEvent.click(screen.getByRole('button', { name: 'Share' }));

        await waitFor(() =>
            expect(share).toHaveBeenCalledWith({ text: 'Spieltag 7' }),
        );
        expect(writeText).not.toHaveBeenCalled();
    });

    it('copies the text and says „Kopiert“ where there is no share menu', async () => {
        render(<ShareMatchdayButton text="Spieltag 7" />);

        fireEvent.click(screen.getByRole('button', { name: 'Share' }));

        await waitFor(() => expect(toast).toHaveBeenCalledWith('Copied', 'ok'));
        expect(writeText).toHaveBeenCalledWith('Spieltag 7');
    });

    it('copies the text when the share menu fails', async () => {
        withShare(() =>
            Promise.reject(new DOMException('no', 'NotAllowedError')),
        );
        render(<ShareMatchdayButton text="Spieltag 7" />);

        fireEvent.click(screen.getByRole('button', { name: 'Share' }));

        await waitFor(() =>
            expect(writeText).toHaveBeenCalledWith('Spieltag 7'),
        );
    });

    it('does nothing more when the share menu is closed', async () => {
        const share = vi.fn<(data: ShareData) => Promise<void>>();
        share.mockRejectedValue(new DOMException('closed', 'AbortError'));
        withShare(share);
        render(<ShareMatchdayButton text="Spieltag 7" />);

        fireEvent.click(screen.getByRole('button', { name: 'Share' }));

        await waitFor(() => expect(share).toHaveBeenCalled());
        expect(writeText).not.toHaveBeenCalled();
        expect(toast).not.toHaveBeenCalled();
    });
});
