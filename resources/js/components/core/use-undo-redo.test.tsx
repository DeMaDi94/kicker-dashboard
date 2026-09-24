import { act, render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { useState } from 'react';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import {
    HISTORY_DEBOUNCE_MS,
    HISTORY_DEPTH,
    useUndoRedo,
    useUndoRedoShortcuts,
} from './use-undo-redo';

const toasted = vi.hoisted(() => vi.fn());

vi.mock('sonner', () => ({ toast: toasted }));

type Draft = { title: string };

function Harness({ locked = false }: { locked?: boolean }) {
    const [draft, setDraft] = useState<Draft>({ title: '' });
    const history = useUndoRedo<Draft>({ title: '' }, setDraft, { locked });

    useUndoRedoShortcuts(history, true);

    return (
        <>
            <input
                aria-label="Title"
                value={draft.title}
                onChange={(event) => {
                    const next = { title: event.target.value };
                    setDraft(next);
                    history.record(next);
                }}
            />
            <textarea
                aria-label="Note"
                onChange={() => {
                    /* has its own undo */
                }}
            />
            <button
                type="button"
                disabled={!history.canUndo}
                onClick={history.undo}
            >
                Undo
            </button>
            <button
                type="button"
                disabled={!history.canRedo}
                onClick={history.redo}
            >
                Redo
            </button>
            <button
                type="button"
                onClick={() => {
                    const loaded = { title: 'Loaded' };
                    setDraft(loaded);
                    history.reset(loaded);
                }}
            >
                Reload
            </button>
            <output>{draft.title}</output>
        </>
    );
}

beforeEach(() => {
    vi.useFakeTimers({ shouldAdvanceTime: true });
    toasted.mockReset();
});

afterEach(() => {
    vi.useRealTimers();
});

const type = async (
    user: ReturnType<typeof userEvent.setup>,
    text: string,
): Promise<void> => {
    await user.type(screen.getByLabelText('Title'), text);
    await act(async () => {
        vi.advanceTimersByTime(HISTORY_DEBOUNCE_MS);
    });
};

describe('undo and redo over the whole state', () => {
    it('collapses fast typing into one step', async () => {
        const user = userEvent.setup({ advanceTimers: vi.advanceTimersByTime });
        render(<Harness />);

        await type(user, 'Stadt');

        await user.click(screen.getByRole('button', { name: 'Undo' }));

        expect(screen.getByRole('status').textContent).toBe('');
        expect(screen.getByRole('button', { name: 'Undo' })).toBeDisabled();
    });

    it('clears redo as soon as something new is changed', async () => {
        const user = userEvent.setup({ advanceTimers: vi.advanceTimersByTime });
        render(<Harness />);

        await type(user, 'A');
        await user.click(screen.getByRole('button', { name: 'Undo' }));
        expect(screen.getByRole('button', { name: 'Redo' })).toBeEnabled();

        await type(user, 'B');

        expect(screen.getByRole('button', { name: 'Redo' })).toBeDisabled();
    });

    it('confirms both directions with a toast', async () => {
        const user = userEvent.setup({ advanceTimers: vi.advanceTimersByTime });
        render(<Harness />);

        await type(user, 'A');
        await user.click(screen.getByRole('button', { name: 'Undo' }));
        await user.click(screen.getByRole('button', { name: 'Redo' }));

        expect(toasted.mock.calls.map((call) => call[0])).toEqual([
            '✓ Undone',
            '✓ Redone',
        ]);
    });

    it('is disabled while the state is locked', async () => {
        const user = userEvent.setup({ advanceTimers: vi.advanceTimersByTime });
        render(<Harness locked />);

        await type(user, 'A');

        expect(screen.getByRole('button', { name: 'Undo' })).toBeDisabled();
    });

    /* (Re)loading starts a fresh history, so a version someone else saved is
       not one undo away from being reverted. */
    it('starts a fresh history when the state is loaded again', async () => {
        const user = userEvent.setup({ advanceTimers: vi.advanceTimersByTime });
        render(<Harness />);

        await type(user, 'A');
        await user.click(screen.getByRole('button', { name: 'Reload' }));
        await act(async () => {
            vi.advanceTimersByTime(HISTORY_DEBOUNCE_MS);
        });

        expect(screen.getByRole('button', { name: 'Undo' })).toBeDisabled();
        expect(screen.getByRole('status').textContent).toBe('Loaded');
    });

    it('keeps 80 steps', () => {
        expect(HISTORY_DEPTH).toBe(80);
        expect(HISTORY_DEBOUNCE_MS).toBe(450);
    });
});

describe('the keyboard shortcuts', () => {
    it('undoes on Ctrl+Z and redoes on Ctrl+Shift+Z and Ctrl+Y', async () => {
        const user = userEvent.setup({ advanceTimers: vi.advanceTimersByTime });
        render(<Harness />);

        await type(user, 'A');
        await user.keyboard('{Control>}z{/Control}');
        expect(screen.getByRole('status').textContent).toBe('');

        await user.keyboard('{Control>}{Shift>}z{/Shift}{/Control}');
        expect(screen.getByRole('status').textContent).toBe('A');

        await user.keyboard('{Control>}z{/Control}');
        await user.keyboard('{Control>}y{/Control}');
        expect(screen.getByRole('status').textContent).toBe('A');
    });

    it('stays out of a textarea, which has its own undo', async () => {
        const user = userEvent.setup({ advanceTimers: vi.advanceTimersByTime });
        render(<Harness />);

        await type(user, 'A');
        await user.click(screen.getByLabelText('Note'));
        await user.keyboard('{Control>}z{/Control}');

        expect(screen.getByRole('status').textContent).toBe('A');
    });
});
