import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { useState } from 'react';
import { describe, expect, it, vi } from 'vitest';
import { DialogProvider, useAlert, useConfirm, usePrompt } from './dialogs';

function Harness({
    run,
}: {
    run: (api: {
        confirm: ReturnType<typeof useConfirm>;
        prompt: ReturnType<typeof usePrompt>;
        alert: ReturnType<typeof useAlert>;
    }) => Promise<unknown>;
}) {
    const confirm = useConfirm();
    const prompt = usePrompt();
    const alert = useAlert();
    const [answer, setAnswer] = useState<string>('–');

    return (
        <>
            <button
                type="button"
                onClick={() =>
                    run({ confirm, prompt, alert }).then((value) =>
                        setAnswer(JSON.stringify(value ?? null)),
                    )
                }
            >
                open
            </button>
            <output>{answer}</output>
        </>
    );
}

const open = async (
    run: Parameters<typeof Harness>[0]['run'],
): Promise<ReturnType<typeof userEvent.setup>> => {
    const user = userEvent.setup();

    render(
        <DialogProvider>
            <Harness run={run} />
        </DialogProvider>,
    );

    await user.click(screen.getByRole('button', { name: 'open' }));

    return user;
};

describe('confirmations', () => {
    it('asks „Confirm“ and resolves true on the primary action', async () => {
        const user = await open(({ confirm }) => confirm('Really?'));

        expect(await screen.findByText('Confirm')).toBeInTheDocument();
        expect(screen.getByText('Really?')).toBeInTheDocument();

        await user.click(screen.getByRole('button', { name: 'OK' }));

        expect(screen.getByRole('status').textContent).toBe('true');
    });

    it('uses the destructive labels when danger is set', async () => {
        await open(({ confirm }) =>
            confirm('Delete the row?', { danger: true }),
        );

        expect(await screen.findByText('Confirm deletion')).toBeInTheDocument();
        expect(
            screen.getByRole('button', { name: 'Delete' }),
        ).toBeInTheDocument();
    });

    it('takes the caller’s own title and labels', async () => {
        await open(({ confirm }) =>
            confirm('Really?', {
                title: 'Archive',
                okLabel: 'Archive now',
                cancelLabel: 'Keep',
            }),
        );

        expect(await screen.findByText('Archive')).toBeInTheDocument();
        expect(
            screen.getByRole('button', { name: 'Archive now' }),
        ).toBeInTheDocument();
        expect(
            screen.getByRole('button', { name: 'Keep' }),
        ).toBeInTheDocument();
    });

    it('cancels on „Cancel“ and on Escape', async () => {
        const user = await open(({ confirm }) => confirm('Really?'));

        await user.click(screen.getByRole('button', { name: 'Cancel' }));
        expect(screen.getByRole('status').textContent).toBe('false');

        await user.click(screen.getByRole('button', { name: 'open' }));
        await screen.findByRole('dialog');
        await user.keyboard('{Escape}');

        await waitFor(() =>
            expect(screen.queryByRole('dialog')).not.toBeInTheDocument(),
        );
        expect(screen.getByRole('status').textContent).toBe('false');
    });

    it('puts focus on the primary action and accepts with Enter', async () => {
        const user = await open(({ confirm }) => confirm('Really?'));

        await waitFor(() =>
            expect(screen.getByRole('button', { name: 'OK' })).toHaveFocus(),
        );

        await user.keyboard('{Enter}');

        expect(screen.getByRole('status').textContent).toBe('true');
    });
});

describe('prompts', () => {
    it('offers „Input“ / „Apply“ and returns the value', async () => {
        const user = await open(({ prompt }) => prompt('New name', 'Draft 1'));

        expect(await screen.findByText('Input')).toBeInTheDocument();

        const field = screen.getByLabelText('New name');
        expect(field).toHaveValue('Draft 1');
        await waitFor(() => expect(field).toHaveFocus());

        await user.clear(field);
        await user.type(field, 'Draft 2');
        await user.click(screen.getByRole('button', { name: 'Apply' }));

        expect(screen.getByRole('status').textContent).toBe('"Draft 2"');
    });

    it('returns null when it is cancelled', async () => {
        const user = await open(({ prompt }) => prompt('New name', 'x'));

        await user.click(screen.getByRole('button', { name: 'Cancel' }));

        expect(screen.getByRole('status').textContent).toBe('null');
    });
});

describe('alerts', () => {
    it('shows „Notice“ with a single OK', async () => {
        const user = await open(({ alert }) => alert('Not possible.'));

        expect(await screen.findByText('Notice')).toBeInTheDocument();
        expect(
            screen.queryByRole('button', { name: 'Cancel' }),
        ).not.toBeInTheDocument();

        await user.click(screen.getByRole('button', { name: 'OK' }));

        await waitFor(() =>
            expect(screen.queryByRole('dialog')).not.toBeInTheDocument(),
        );
    });
});

describe('outside the provider', () => {
    it('says what is missing', () => {
        function Orphan() {
            useConfirm();

            return null;
        }

        // React logs the error it rethrows; the assertion is on the throw.
        const logged = vi.spyOn(console, 'error').mockImplementation(() => {});

        expect(() => render(<Orphan />)).toThrow(
            'useConfirm/usePrompt/useAlert must be used within DialogProvider.',
        );

        logged.mockRestore();
    });
});

/* Never the browser's: no screen reaches for the native dialogs. */
describe('no native dialogs', () => {
    const sources = import.meta.glob<string>(
        ['/resources/js/**/*.{ts,tsx}', '!/resources/js/**/*.test.{ts,tsx}'],
        { query: '?raw', import: 'default', eager: true },
    );

    it('scans the whole app', () => {
        expect(
            Object.keys(sources).some((path) =>
                path.endsWith('/components/core/dialogs.tsx'),
            ),
        ).toBe(true);
    });

    it('calls no window.confirm, window.prompt or window.alert', () => {
        const offenders = Object.entries(sources)
            .filter(([, source]) =>
                /\b(window|globalThis)\s*\.\s*(confirm|prompt|alert)\s*\(/.test(
                    source,
                ),
            )
            .map(([path]) => path);

        expect(offenders).toEqual([]);
    });
});
