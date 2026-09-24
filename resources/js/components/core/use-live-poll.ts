import { router } from '@inertiajs/react';
import { useEffect, useRef } from 'react';

/*
 * Open sessions converge without a reload: every 6 s (docs/DECISIONS.md B9)
 * the page asks the server again for the props that can change under it.
 *
 * The tick does nothing while the tab is not visible. Inertia's own `usePoll`
 * only slows down in a background tab, so the check is made here, on every
 * tick.
 *
 * The reload is partial (`only`) and keeps scroll position and component
 * state. What a page does with props that arrive while its user is editing is
 * the page's decision; this hook only fetches them.
 */

export const POLL_INTERVAL_MS = 6000;

export function useLivePoll(only: readonly string[]): void {
    const props = useRef(only);

    useEffect(() => {
        props.current = only;
    });

    useEffect(() => {
        const tick = setInterval(() => {
            if (document.visibilityState !== 'visible') {
                return;
            }

            router.reload({ only: [...props.current] });
        }, POLL_INTERVAL_MS);

        return () => clearInterval(tick);
    }, []);
}
