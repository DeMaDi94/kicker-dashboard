import { router } from '@inertiajs/react';
import { useCallback, useEffect, useRef, useState } from 'react';
import type { ListFilters } from '@/types/list';

/*
 * A paged list's search, filters and page live in the URL, so a reload or a
 * shared link keeps the view.
 *
 * The inputs are held here, seeded once from the server's `filters`: a poll
 * or the answer to an earlier keystroke must not overwrite what is being
 * typed. Typing is sent 300 ms after the last key (`typeFilter`,
 * docs/DECISIONS.md B9); a select or a checkbox goes at once (`setFilter`).
 * Either goes back to page 1. The visit keeps the component
 * (`preserveState`), so the search field keeps its focus, and replaces the
 * history entry rather than adding one per keystroke.
 *
 * Every visit first cancels whatever is in flight: a poll that left before
 * the search must not land after it with the old page's rows.
 */

export const LIST_SEARCH_DEBOUNCE_MS = 300;

export function useListQuery<Filters extends ListFilters>(
    filters: Filters,
    only: readonly string[],
): {
    values: Filters;
    setFilter: (key: keyof Filters, value: string) => void;
    typeFilter: (key: keyof Filters, value: string) => void;
    setPage: (page: number) => void;
} {
    const [values, setValues] = useState<Filters>(filters);
    const latest = useRef(values);
    const onlyRef = useRef(only);
    const timer = useRef<ReturnType<typeof setTimeout> | null>(null);

    useEffect(() => {
        onlyRef.current = only;
    });

    const clear = () => {
        if (timer.current !== null) {
            clearTimeout(timer.current);
            timer.current = null;
        }
    };

    useEffect(() => clear, []);

    const visit = useCallback((next: Filters, page: number) => {
        clear();

        const query: Record<string, string | number> = {};

        for (const [key, value] of Object.entries(next)) {
            if (value !== '') {
                query[key] = value;
            }
        }

        if (page > 1) {
            query.page = page;
        }

        router.cancelAll();
        router.get(window.location.pathname, query, {
            only: [...onlyRef.current],
            preserveState: true,
            replace: true,
        });
    }, []);

    const change = (key: keyof Filters, value: string): Filters => {
        const next = { ...latest.current, [key]: value };
        latest.current = next;
        setValues(next);

        return next;
    };

    const setFilter = useCallback(
        (key: keyof Filters, value: string) => visit(change(key, value), 1),
        [visit],
    );

    const typeFilter = useCallback(
        (key: keyof Filters, value: string) => {
            const next = change(key, value);
            clear();
            timer.current = setTimeout(
                () => visit(next, 1),
                LIST_SEARCH_DEBOUNCE_MS,
            );
        },
        [visit],
    );

    const setPage = useCallback(
        (page: number) => visit(latest.current, page),
        [visit],
    );

    return { values, setFilter, typeFilter, setPage };
}
