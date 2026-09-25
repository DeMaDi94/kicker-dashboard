import { render, screen } from '@testing-library/react';
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
        expect(screen.getAllByRole('columnheader')).toHaveLength(25);
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
    // D14 had it last; LOG-03 puts „Verlauf“ after it (D17).
    it('is shown only with visits.view, directly before „Verlauf“ (D14, LOG-03)', () => {
        const visits = NAV_ITEMS.findIndex((item) => item.title === 'Visitors');

        expect(NAV_ITEMS[visits]?.permission).toBe('visits.view');
        expect(NAV_ITEMS[visits]?.sections).toEqual(['/visits']);
        expect(NAV_ITEMS[visits + 1]?.title).toBe('History');
    });
});
