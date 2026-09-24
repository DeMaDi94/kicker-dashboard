import { useCallback, useEffect, useRef, useState } from 'react';
import { toast } from '@/components/core/toast';
import { useTranslation } from '@/hooks/use-translation';

/*
 * Undo and redo over the whole state, not per field.
 *
 * Each step is a JSON snapshot of the complete state. Fast typing collapses
 * into one step with a 450 ms debounce, 80 steps are kept (docs/DECISIONS.md
 * B9), any new change clears redo, and nothing runs while the state is
 * locked. Both actions confirm with a toast.
 *
 * The keyboard part is deliberately not wired in here: whether the shortcuts
 * apply depends on the screen, so the page that owns that condition calls
 * `useUndoRedoShortcuts`.
 */

export const HISTORY_DEPTH = 80;
export const HISTORY_DEBOUNCE_MS = 450;

type UndoRedo<T> = {
    /** Record a change; repeated calls within the debounce collapse. */
    record: (state: T) => void;
    undo: () => void;
    redo: () => void;
    /** A fresh history from `state`, when the state is (re)loaded. */
    reset: (state: T) => void;
    canUndo: boolean;
    canRedo: boolean;
};

export function useUndoRedo<T>(
    initial: T,
    apply: (state: T) => void,
    options: { locked?: boolean } = {},
): UndoRedo<T> {
    const { t } = useTranslation();
    const locked = options.locked ?? false;

    const baseline = useRef(JSON.stringify(initial));
    const undoStack = useRef<string[]>([]);
    const redoStack = useRef<string[]>([]);
    const timer = useRef<ReturnType<typeof setTimeout> | null>(null);
    const [depths, setDepths] = useState({ undo: 0, redo: 0 });

    const publish = () =>
        setDepths({
            undo: undoStack.current.length,
            redo: redoStack.current.length,
        });

    const commit = useCallback((next: string) => {
        // No real change: fast typing that came back to where it started.
        if (next === baseline.current) {
            return;
        }

        undoStack.current.push(baseline.current);

        if (undoStack.current.length > HISTORY_DEPTH) {
            undoStack.current.shift();
        }

        redoStack.current = [];
        baseline.current = next;
        publish();
    }, []);

    const record = useCallback(
        (state: T) => {
            if (locked) {
                return;
            }

            const next = JSON.stringify(state);

            if (timer.current !== null) {
                clearTimeout(timer.current);
            }

            timer.current = setTimeout(() => commit(next), HISTORY_DEBOUNCE_MS);
        },
        [commit, locked],
    );

    useEffect(
        () => () => {
            if (timer.current !== null) {
                clearTimeout(timer.current);
            }
        },
        [],
    );

    const reset = useCallback((state: T) => {
        if (timer.current !== null) {
            clearTimeout(timer.current);
            timer.current = null;
        }

        baseline.current = JSON.stringify(state);
        undoStack.current = [];
        redoStack.current = [];
        publish();
    }, []);

    const step = useCallback(
        (from: 'undo' | 'redo') => {
            const source = from === 'undo' ? undoStack : redoStack;
            const target = from === 'undo' ? redoStack : undoStack;

            if (locked || source.current.length === 0) {
                return;
            }

            if (timer.current !== null) {
                clearTimeout(timer.current);
                timer.current = null;
            }

            const next = source.current.pop();

            if (next === undefined) {
                return;
            }

            target.current.push(baseline.current);
            baseline.current = next;
            apply(JSON.parse(next));
            publish();

            toast(from === 'undo' ? t('Undone') : t('Redone'), 'ok');
        },
        [apply, locked, t],
    );

    return {
        record,
        undo: useCallback(() => step('undo'), [step]),
        redo: useCallback(() => step('redo'), [step]),
        reset,
        canUndo: !locked && depths.undo > 0,
        canRedo: !locked && depths.redo > 0,
    };
}

/**
 * Ctrl/Cmd+Z undoes, Ctrl/Cmd+Shift+Z and Ctrl+Y redo — only while `active`,
 * and never inside a textarea, which has its own undo.
 */
export function useUndoRedoShortcuts(
    { undo, redo }: Pick<UndoRedo<unknown>, 'undo' | 'redo'>,
    active: boolean,
): void {
    useEffect(() => {
        if (!active) {
            return;
        }

        const onKeyDown = (event: KeyboardEvent) => {
            if (!event.ctrlKey && !event.metaKey) {
                return;
            }

            const target = event.target;

            if (
                target instanceof HTMLElement &&
                target.tagName.toLowerCase() === 'textarea'
            ) {
                return;
            }

            const key = event.key.toLowerCase();

            if (key === 'z' && !event.shiftKey) {
                event.preventDefault();
                undo();
            } else if ((key === 'z' && event.shiftKey) || key === 'y') {
                event.preventDefault();
                redo();
            }
        };

        document.addEventListener('keydown', onKeyDown);

        return () => document.removeEventListener('keydown', onKeyDown);
    }, [undo, redo, active]);
}
