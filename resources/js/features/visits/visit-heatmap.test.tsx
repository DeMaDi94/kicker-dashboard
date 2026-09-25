import { render, screen, within } from '@testing-library/react';
import { describe, expect, it } from 'vitest';
import { NAV_ITEMS } from '@/layouts/shell/nav-items';
import { VisitHeatmap } from './visit-heatmap';

const heatmap = Array.from({ length: 7 }, (_, weekday) =>
    Array.from({ length: 24 }, (_, hour) =>
        weekday === 0 && hour === 9 ? 4 : 0,
    ),
);

describe('VIS-06 · the heatmap of visits by weekday and hour', () => {
    it('has a row per weekday, Monday first, and a column per hour', () => {
        render(<VisitHeatmap heatmap={heatmap} />);

        const rows = screen.getAllByRole('rowheader');

        expect(rows).toHaveLength(7);
        expect(rows[0]).toHaveTextContent(/^Mon/);
        expect(rows[6]).toHaveTextContent(/^Sun/);
        expect(
            within(screen.getAllByRole('row')[0]).getAllByRole('columnheader'),
        ).toHaveLength(25);
    });

    it('gives every cell its count in words, not only in its shade', () => {
        render(<VisitHeatmap heatmap={heatmap} />);

        expect(
            screen.getByTitle(/^Mon, 9 o’clock: 4 visits$/).style.background,
        ).toContain('var(--chart-1) 100%');
        expect(
            screen.getByTitle(/^Sun, 23 o’clock: 0 visits$/),
        ).toBeInTheDocument();
    });
});

describe('VIS-04 · „Besucher“ in the navigation', () => {
    it('is the last entry, shown only with visits.view (D14)', () => {
        const last = NAV_ITEMS[NAV_ITEMS.length - 1];

        expect(last?.title).toBe('Visitors');
        expect(last?.permission).toBe('visits.view');
        expect(last?.sections).toEqual(['/visits']);
    });
});
