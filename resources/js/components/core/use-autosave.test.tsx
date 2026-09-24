import { act, render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { useState } from 'react';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import {
    AUTOSAVE_DEBOUNCE_MS,
    useAutosave,
    type AutosaveContext,
} from './use-autosave';

type Draft = { title: string };
type Save = (draft: Draft, context: AutosaveContext) => void | Promise<unknown>;

let settleNow: (() => Promise<void>) | null = null;

function Harness({
    save,
    locked = false,
    conflictPending = false,
}: {
    save: Save;
    locked?: boolean;
    conflictPending?: boolean;
}) {
    const [draft, setDraft] = useState<Draft>({ title: '' });
    const { flush, settle } = useAutosave(draft, save, {
        locked,
        conflictPending,
    });
    settleNow = settle;

    return (
        <>
            <input
                aria-label="Title"
                value={draft.title}
                onChange={(event) => setDraft({ title: event.target.value })}
            />
            <button type="button" onClick={flush}>
                leave
            </button>
        </>
    );
}

const later = async (ms: number) => {
    await act(async () => {
        vi.advanceTimersByTime(ms);
    });
};

beforeEach(() => {
    vi.useFakeTimers({ shouldAdvanceTime: true });
});

afterEach(() => {
    vi.useRealTimers();
    settleNow = null;
});

describe('autosave', () => {
    it('waits for the typing to stop and then writes once', async () => {
        const save = vi.fn();
        const user = userEvent.setup({ advanceTimers: vi.advanceTimersByTime });
        render(<Harness save={save} />);

        await user.type(screen.getByLabelText('Title'), 'City');
        expect(save).not.toHaveBeenCalled();

        await later(AUTOSAVE_DEBOUNCE_MS);

        expect(save).toHaveBeenCalledTimes(1);
        expect(save).toHaveBeenCalledWith(
            { title: 'City' },
            { leaving: false },
        );
        expect(AUTOSAVE_DEBOUNCE_MS).toBe(300);
    });

    it('does not save the state it was handed on mount', async () => {
        const save = vi.fn();
        render(<Harness save={save} />);

        await later(AUTOSAVE_DEBOUNCE_MS * 2);

        expect(save).not.toHaveBeenCalled();
    });

    it('never starts a save while the previous one is in flight, and sends the last change after it', async () => {
        let finish: () => void = () => undefined;
        const save = vi.fn(
            () =>
                new Promise<void>((resolve) => {
                    finish = resolve;
                }),
        );
        const user = userEvent.setup({ advanceTimers: vi.advanceTimersByTime });
        render(<Harness save={save} />);

        await user.type(screen.getByLabelText('Title'), 'City');
        await later(AUTOSAVE_DEBOUNCE_MS);
        expect(save).toHaveBeenCalledTimes(1);

        await user.type(screen.getByLabelText('Title'), ' Hall');
        await later(AUTOSAVE_DEBOUNCE_MS * 3);
        expect(save).toHaveBeenCalledTimes(1);

        await act(async () => finish());

        expect(save).toHaveBeenCalledTimes(2);
        expect(save).toHaveBeenLastCalledWith(
            { title: 'City Hall' },
            { leaving: false },
        );
    });

    it('settles once nothing is pending or in flight', async () => {
        let finish: () => void = () => undefined;
        const save = vi.fn(
            () =>
                new Promise<void>((resolve) => {
                    finish = resolve;
                }),
        );
        const user = userEvent.setup({ advanceTimers: vi.advanceTimersByTime });
        render(<Harness save={save} />);

        await user.type(screen.getByLabelText('Title'), 'City');

        let settled = false;
        await act(async () => {
            void settleNow?.().then(() => {
                settled = true;
            });
        });

        // The pending change went out at once, without waiting 300 ms.
        expect(save).toHaveBeenCalledTimes(1);
        expect(settled).toBe(false);

        await act(async () => finish());

        expect(settled).toBe(true);
    });
});

describe('autosave is suppressed while locked and while a conflict waits', () => {
    it.each([
        ['locked', { locked: true }],
        ['conflict pending', { conflictPending: true }],
    ])('writes nothing while %s', async (_, suppression) => {
        const save = vi.fn();
        const user = userEvent.setup({ advanceTimers: vi.advanceTimersByTime });
        render(<Harness save={save} {...suppression} />);

        await user.type(screen.getByLabelText('Title'), 'City');
        await later(AUTOSAVE_DEBOUNCE_MS);
        await user.click(screen.getByRole('button', { name: 'leave' }));
        act(() => {
            document.dispatchEvent(new Event('visibilitychange'));
        });

        expect(save).not.toHaveBeenCalled();
    });

    it('drops what was scheduled when the suppression starts', async () => {
        const save = vi.fn();
        const user = userEvent.setup({ advanceTimers: vi.advanceTimersByTime });
        const { rerender } = render(<Harness save={save} />);

        await user.type(screen.getByLabelText('Title'), 'City');
        rerender(<Harness save={save} locked />);
        await later(AUTOSAVE_DEBOUNCE_MS);

        expect(save).not.toHaveBeenCalled();
    });

    it('saves the current state again once the conflict is resolved', async () => {
        const save = vi.fn();
        const user = userEvent.setup({ advanceTimers: vi.advanceTimersByTime });
        const { rerender } = render(<Harness save={save} conflictPending />);

        await user.type(screen.getByLabelText('Title'), 'City');
        rerender(<Harness save={save} />);
        await later(AUTOSAVE_DEBOUNCE_MS);

        expect(save).toHaveBeenCalledWith(
            { title: 'City' },
            { leaving: false },
        );
    });
});

describe('nothing is lost by leaving', () => {
    it('flushes the pending write when the screen is left', async () => {
        const save = vi.fn();
        const user = userEvent.setup({ advanceTimers: vi.advanceTimersByTime });
        render(<Harness save={save} />);

        await user.type(screen.getByLabelText('Title'), 'City');
        await user.click(screen.getByRole('button', { name: 'leave' }));

        expect(save).toHaveBeenCalledWith({ title: 'City' }, { leaving: true });

        await later(AUTOSAVE_DEBOUNCE_MS);

        expect(save).toHaveBeenCalledTimes(1);
    });

    it('flushes when the tab is hidden', async () => {
        const save = vi.fn();
        const user = userEvent.setup({ advanceTimers: vi.advanceTimersByTime });
        render(<Harness save={save} />);

        await user.type(screen.getByLabelText('Title'), 'City');

        const visibility = vi
            .spyOn(document, 'visibilityState', 'get')
            .mockReturnValue('hidden');
        act(() => {
            document.dispatchEvent(new Event('visibilitychange'));
        });
        visibility.mockRestore();

        expect(save).toHaveBeenCalledWith({ title: 'City' }, { leaving: true });
    });

    it.each(['pagehide', 'beforeunload'])('flushes on %s', async (event) => {
        const save = vi.fn();
        const user = userEvent.setup({
            advanceTimers: vi.advanceTimersByTime,
        });
        render(<Harness save={save} />);

        await user.type(screen.getByLabelText('Title'), 'City');
        act(() => {
            window.dispatchEvent(new Event(event));
        });

        expect(save).toHaveBeenCalledTimes(1);
        expect(save).toHaveBeenCalledWith({ title: 'City' }, { leaving: true });
    });

    it('flushes when the component unmounts — leaving the screen inside the app', async () => {
        const save = vi.fn();
        const user = userEvent.setup({ advanceTimers: vi.advanceTimersByTime });
        const { unmount } = render(<Harness save={save} />);

        await user.type(screen.getByLabelText('Title'), 'City');
        unmount();

        expect(save).toHaveBeenCalledWith({ title: 'City' }, { leaving: true });
    });

    it('does not listen for input on the document — only the state passed in is saved', async () => {
        const save = vi.fn();
        render(<Harness save={save} />);

        const outside = document.createElement('input');
        document.body.appendChild(outside);
        act(() => {
            outside.dispatchEvent(new Event('input', { bubbles: true }));
            outside.dispatchEvent(new Event('change', { bubbles: true }));
        });
        await later(AUTOSAVE_DEBOUNCE_MS);
        act(() => {
            window.dispatchEvent(new Event('pagehide'));
        });
        outside.remove();

        expect(save).not.toHaveBeenCalled();
    });
});
