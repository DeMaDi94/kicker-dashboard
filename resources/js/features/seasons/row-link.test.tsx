import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, describe, expect, it, vi } from 'vitest';

const visit = vi.fn();
vi.mock('@inertiajs/react', () => ({ router: { visit } }));

const { visitRow } = await import('./row-link');

function Row() {
    return (
        <table>
            <tbody>
                <tr onClick={visitRow('/players/7')}>
                    <td>
                        <a href="/players/7">Anna</a>
                    </td>
                    <td>55</td>
                </tr>
            </tbody>
        </table>
    );
}

beforeEach(() => visit.mockClear());

describe('STAT-01 · a player row leads to the player page', () => {
    it('opens the player page from anywhere in the row', async () => {
        render(<Row />);

        await userEvent.click(screen.getByText('55'));

        expect(visit).toHaveBeenCalledWith('/players/7');
    });

    it('leaves a click on the name to the link itself', async () => {
        render(<Row />);

        await userEvent.click(screen.getByRole('link', { name: 'Anna' }));

        expect(visit).not.toHaveBeenCalled();
    });
});
