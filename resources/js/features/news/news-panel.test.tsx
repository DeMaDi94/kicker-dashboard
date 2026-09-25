import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it } from 'vitest';
import { DialogProvider } from '@/components/core/dialogs';
import { NewsPanel } from './news-panel';
import type { NewsLine } from './types';

const post = (id: number, overrides: Partial<NewsLine> = {}): NewsLine => ({
    id,
    text: `Post ${id}`,
    authorName: 'Dennis',
    postedAt: '2026-09-25T10:00:00+02:00',
    edited: false,
    mayChange: false,
    ...overrides,
});

const panel = (news: NewsLine[], canWrite = false) =>
    render(
        <DialogProvider>
            <NewsPanel seasonId={1} news={news} canWrite={canWrite} />
        </DialogProvider>,
    );

describe('NEWS-02 · the newest three are shown, older ones behind „Alle anzeigen“', () => {
    it('shows three and opens the rest on request', async () => {
        const user = userEvent.setup();
        panel([post(5), post(4), post(3), post(2), post(1)]);

        expect(screen.getByText('Post 5')).toBeInTheDocument();
        expect(screen.getByText('Post 3')).toBeInTheDocument();
        expect(screen.queryByText('Post 2')).not.toBeInTheDocument();

        await user.click(screen.getByRole('button', { name: 'Show all' }));

        expect(screen.getByText('Post 1')).toBeInTheDocument();
        expect(
            screen.queryByRole('button', { name: 'Show all' }),
        ).not.toBeInTheDocument();
    });

    it('offers nothing to open with three or fewer', () => {
        panel([post(3), post(2), post(1)]);

        expect(
            screen.queryByRole('button', { name: 'Show all' }),
        ).not.toBeInTheDocument();
    });

    it('shows the date in German time, the author and whether it was edited (D17)', () => {
        panel([post(1, { edited: true })]);

        // The locale is the test's; the Berlin calendar day holds in any.
        expect(screen.getByText(/25.*2026/)).toBeInTheDocument();
        expect(screen.getByText('Dennis')).toBeInTheDocument();
        expect(screen.getByText('(edited)')).toBeInTheDocument();
    });
});

describe('NEWS-01 · plain text with clickable links', () => {
    it('keeps the line breaks and links the address', () => {
        panel([post(1, { text: 'Zeile 1\nMehr: https://example.org' })]);

        const link = screen.getByRole('link', { name: 'https://example.org' });

        expect(link).toHaveAttribute('href', 'https://example.org');
        expect(link.closest('p')?.textContent).toBe(
            'Zeile 1\nMehr: https://example.org',
        );
    });

    it('offers writing only to a signed-in user', () => {
        const { unmount } = panel([], false);
        expect(
            screen.queryByRole('button', { name: 'Write news' }),
        ).not.toBeInTheDocument();
        unmount();

        panel([], true);
        expect(
            screen.getByRole('button', { name: 'Write news' }),
        ).toBeInTheDocument();
    });
});

describe('NEWS-03 · changing and deleting only where allowed', () => {
    it('shows edit and delete only on posts the viewer may change', () => {
        panel([post(2, { mayChange: true }), post(1)]);

        expect(screen.getAllByRole('button', { name: 'Edit' })).toHaveLength(1);
        expect(screen.getAllByRole('button', { name: 'Delete' })).toHaveLength(
            1,
        );
    });

    it('asks before deleting (D17)', async () => {
        const user = userEvent.setup();
        panel([post(1, { mayChange: true })]);

        await user.click(screen.getByRole('button', { name: 'Delete' }));

        expect(screen.getByText('Delete news post?')).toBeInTheDocument();
    });
});
