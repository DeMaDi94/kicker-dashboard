import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';
import { ListPager } from './list-pager';

describe('the pager under a paged list', () => {
    it('reads as „‹ Previous · Page N of M · Next ›“', () => {
        render(<ListPager page={2} lastPage={5} onPageChange={vi.fn()} />);

        expect(screen.getByRole('navigation').textContent).toBe(
            '‹ Previous·Page 2 of 5·Next ›',
        );
    });

    it('moves one page either way', async () => {
        const user = userEvent.setup();
        const onPageChange = vi.fn();
        render(<ListPager page={2} lastPage={5} onPageChange={onPageChange} />);

        await user.click(screen.getByRole('button', { name: 'Previous' }));
        await user.click(screen.getByRole('button', { name: 'Next' }));

        expect(onPageChange.mock.calls).toEqual([[1], [3]]);
    });

    it('cannot go before the first or past the last page', () => {
        const { rerender } = render(
            <ListPager page={1} lastPage={3} onPageChange={vi.fn()} />,
        );

        expect(screen.getByRole('button', { name: 'Previous' })).toBeDisabled();
        expect(screen.getByRole('button', { name: 'Next' })).toBeEnabled();

        rerender(<ListPager page={3} lastPage={3} onPageChange={vi.fn()} />);

        expect(screen.getByRole('button', { name: 'Previous' })).toBeEnabled();
        expect(screen.getByRole('button', { name: 'Next' })).toBeDisabled();
    });
});
