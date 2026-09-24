import { useCallback, useEffect, useRef } from 'react';

/*
 * The state saves itself as it is edited, 300 ms after the last change
 * (docs/DECISIONS.md B9), and nothing is lost by leaving.
 *
 * Saving is scoped to the state the caller passes in — never a document-level
 * `input`/`change` listener, which would let any control anywhere on the page
 * trigger a write. The two reasons not to save are explicit options:
 *
 * - `locked` — the record is read-only; nothing is saved. A page that must
 *   save while locked simply does not pass it.
 * - `conflictPending` — the server reported a conflicting change and the user
 *   has not yet chosen how to resolve it; nothing is saved until they have.
 *   Once it clears, the current state goes out again.
 *
 * The leave listeners below are not input listeners: they only flush the
 * state this hook already holds — when the tab is hidden, on `pagehide` and
 * `beforeunload`, and on unmount, which is leaving the screen inside the app.
 *
 * Saves never overlap: a change made while one is in flight goes out when it
 * has landed, so the second is based on what the first stored. Only leaving
 * cannot wait. `settle()` waits until nothing is pending or in flight, for a
 * write elsewhere on the page that must come after this one.
 */

export const AUTOSAVE_DEBOUNCE_MS = 300;

export type AutosaveContext = {
    /** The page is being left: the write has to survive the unload. */
    leaving: boolean;
};

export function useAutosave<T>(
    state: T,
    save: (state: T, context: AutosaveContext) => void | Promise<unknown>,
    options: { locked?: boolean; conflictPending?: boolean } = {},
): { flush: () => void; settle: () => Promise<void> } {
    const suppressed =
        (options.locked ?? false) || (options.conflictPending ?? false);
    const timer = useRef<ReturnType<typeof setTimeout> | null>(null);
    const pending = useRef<{ state: T } | null>(null);
    const busy = useRef(false);
    const current = useRef<Promise<void>>(Promise.resolve());
    const saveRef = useRef(save);
    const suppressedRef = useRef(suppressed);
    const first = useRef(true);

    useEffect(() => {
        saveRef.current = save;
        suppressedRef.current = suppressed;
    });

    const clear = () => {
        if (timer.current !== null) {
            clearTimeout(timer.current);
            timer.current = null;
        }
    };

    const run = useCallback((next: T, leaving: boolean) => {
        if (busy.current && !leaving) {
            pending.current = { state: next };

            return;
        }

        pending.current = null;

        if (leaving) {
            void saveRef.current(next, { leaving });

            return;
        }

        busy.current = true;
        current.current = Promise.resolve()
            .then(() => saveRef.current(next, { leaving }))
            .then(
                () => undefined,
                () => undefined,
            )
            .finally(() => {
                busy.current = false;
                const queued = pending.current;

                if (queued !== null && !suppressedRef.current) {
                    run(queued.state, false);
                }
            });
    }, []);

    const flushWith = useCallback(
        (leaving: boolean) => {
            clear();

            if (pending.current === null || suppressedRef.current) {
                return;
            }

            run(pending.current.state, leaving);
        },
        [run],
    );

    /** Write now — leaving the screen must not lose the last 300 ms. */
    const flush = useCallback(() => flushWith(true), [flushWith]);

    const settle = useCallback(async () => {
        clear();

        while (
            busy.current ||
            (pending.current !== null && !suppressedRef.current)
        ) {
            if (!busy.current && pending.current !== null) {
                run(pending.current.state, false);
            }

            await current.current;
        }
    }, [run]);

    useEffect(() => {
        // The state as it arrives is what was loaded, not an edit.
        if (first.current) {
            first.current = false;

            return;
        }

        if (suppressed) {
            clear();
            pending.current = null;

            return;
        }

        pending.current = { state };
        clear();
        timer.current = setTimeout(() => {
            timer.current = null;
            const queued = pending.current;

            if (queued !== null) {
                run(queued.state, false);
            }
        }, AUTOSAVE_DEBOUNCE_MS);
    }, [state, suppressed, run]);

    useEffect(() => {
        const onVisibility = () => {
            if (document.visibilityState === 'hidden') {
                flushWith(true);
            }
        };
        const onLeave = () => flushWith(true);

        document.addEventListener('visibilitychange', onVisibility);
        window.addEventListener('pagehide', onLeave);
        window.addEventListener('beforeunload', onLeave);

        return () => {
            document.removeEventListener('visibilitychange', onVisibility);
            window.removeEventListener('pagehide', onLeave);
            window.removeEventListener('beforeunload', onLeave);
            // Leaving the screen inside the app.
            flushWith(true);
        };
    }, [flushWith]);

    return { flush, settle };
}
