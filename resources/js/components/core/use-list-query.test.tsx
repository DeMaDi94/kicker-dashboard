import { act, renderHook } from '@testing-library/react';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

const get = vi.fn();
const cancelAll = vi.fn();

vi.mock('@inertiajs/react', () => ({ router: { get, cancelAll } }));

const { LIST_SEARCH_DEBOUNCE_MS, useListQuery } =
    await import('./use-list-query');

const filters = { search: '', status: '' };

beforeEach(() => {
    vi.useFakeTimers();
    get.mockClear();
    cancelAll.mockClear();
    window.history.replaceState(null, '', '/dashboard?page=3');
});

afterEach(() => {
    vi.useRealTimers();
});

describe('search, filters and page live in the URL', () => {
    it('shows what is typed at once and asks after a 300 ms pause', () => {
        const { result } = renderHook(() => useListQuery(filters, ['rows']));

        act(() => result.current.typeFilter('search', 'Be'));
        act(() => {
            vi.advanceTimersByTime(100);
        });
        act(() => result.current.typeFilter('search', 'Berlin'));

        expect(result.current.values.search).toBe('Berlin');

        act(() => {
            vi.advanceTimersByTime(LIST_SEARCH_DEBOUNCE_MS - 1);
        });
        expect(get).not.toHaveBeenCalled();

        act(() => {
            vi.advanceTimersByTime(1);
        });
        expect(LIST_SEARCH_DEBOUNCE_MS).toBe(300);
        expect(get).toHaveBeenCalledTimes(1);
        expect(get).toHaveBeenCalledWith(
            '/dashboard',
            { search: 'Berlin' },
            { only: ['rows'], preserveState: true, replace: true },
        );
    });

    it('goes back to page 1 when a filter changes, and sends it at once', () => {
        const { result } = renderHook(() =>
            useListQuery({ search: 'Berlin', status: '' }, ['rows']),
        );

        act(() => result.current.setFilter('status', 'sent'));

        expect(get).toHaveBeenCalledWith(
            '/dashboard',
            { search: 'Berlin', status: 'sent' },
            expect.objectContaining({ replace: true }),
        );
    });

    it('keeps the search and filters when the page changes', () => {
        const { result } = renderHook(() =>
            useListQuery({ search: 'Berlin', status: 'sent' }, ['rows']),
        );

        act(() => result.current.setPage(2));

        expect(get).toHaveBeenCalledWith(
            '/dashboard',
            { search: 'Berlin', status: 'sent', page: 2 },
            expect.objectContaining({ only: ['rows'] }),
        );
    });

    it('cancels a request in flight before it asks, so an older answer cannot land last', () => {
        const { result } = renderHook(() => useListQuery(filters, ['rows']));

        act(() => result.current.setPage(2));

        expect(cancelAll).toHaveBeenCalledTimes(1);
        expect(cancelAll.mock.invocationCallOrder[0]).toBeLessThan(
            get.mock.invocationCallOrder[0] ?? 0,
        );
    });

    it('does not let new props overwrite what is being typed', () => {
        const { result, rerender } = renderHook(
            ({ server }) => useListQuery(server, ['rows']),
            { initialProps: { server: filters } },
        );

        act(() => result.current.typeFilter('search', 'Berlin'));
        rerender({ server: { search: '', status: '' } });

        expect(result.current.values.search).toBe('Berlin');
    });

    it('drops a search still waiting when the page goes', () => {
        const { result, unmount } = renderHook(() =>
            useListQuery(filters, ['rows']),
        );

        act(() => result.current.typeFilter('search', 'Berlin'));
        unmount();
        act(() => {
            vi.advanceTimersByTime(LIST_SEARCH_DEBOUNCE_MS);
        });

        expect(get).not.toHaveBeenCalled();
    });
});
