import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';
import { KickerPasteDialog } from './kicker-paste-dialog';

const players = [
    { id: 1, name: 'MR', alias: 'MR93' },
    { id: 2, name: 'BK', alias: 'BK' },
];

const text = 'Spieltagswertung\nPlatz\tName\tPunkte\n1\tMR93\t85\n2\tBK\t26';

describe('MD-05 · the kicker paste dialog', () => {
    it('hands the matched points over and closes', async () => {
        const onApply = vi.fn();
        render(<KickerPasteDialog players={players} onApply={onApply} />);

        await userEvent.click(
            screen.getByRole('button', { name: 'Paste from kicker' }),
        );
        await userEvent.click(screen.getByLabelText('Copied league page'));
        await userEvent.paste(text);
        await userEvent.click(screen.getByRole('button', { name: 'Apply' }));

        expect(onApply).toHaveBeenCalledOnce();
        expect([...(onApply.mock.calls[0]?.[0].points ?? [])]).toEqual([
            [1, 85],
            [2, 26],
        ]);
        expect(screen.queryByRole('dialog')).not.toBeInTheDocument();
    });

    it('stays open and says so when the text holds no matchday ranking', async () => {
        const onApply = vi.fn();
        render(<KickerPasteDialog players={players} onApply={onApply} />);

        await userEvent.click(
            screen.getByRole('button', { name: 'Paste from kicker' }),
        );
        await userEvent.click(screen.getByLabelText('Copied league page'));
        await userEvent.paste('Saisonwertung\n1\tMR93\t330');
        await userEvent.click(screen.getByRole('button', { name: 'Apply' }));

        expect(onApply).not.toHaveBeenCalled();
        expect(screen.getByRole('dialog')).toHaveTextContent(
            'The text holds no matchday ranking.',
        );
    });
});
