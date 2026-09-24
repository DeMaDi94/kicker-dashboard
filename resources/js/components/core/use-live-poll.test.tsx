import { renderHook } from '@testing-library/react';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

const reload = vi.fn();

vi.mock('@inertiajs/react', () => ({ router: { reload } }));

const { POLL_INTERVAL_MS, useLivePoll } = await import('./use-live-poll');

beforeEach(() => {
    vi.useFakeTimers();
    reload.mockClear();
});

afterEach(() => {
    vi.useRealTimers();
    vi.restoreAllMocks();
});

describe('open sessions converge without a reload', () => {
    it('asks for the listed props every 6 s', () => {
        renderHook(() => useLivePoll(['rows', 'counts']));

        vi.advanceTimersByTime(POLL_INTERVAL_MS - 1);
        expect(reload).not.toHaveBeenCalled();

        vi.advanceTimersByTime(1);
        expect(reload).toHaveBeenCalledWith({ only: ['rows', 'counts'] });
        expect(POLL_INTERVAL_MS).toBe(6000);
    });

    it('asks for the props named on the latest render', () => {
        const { rerender } = renderHook(({ only }) => useLivePoll(only), {
            initialProps: { only: ['rows'] },
        });

        rerender({ only: ['rows', 'counts'] });
        vi.advanceTimersByTime(POLL_INTERVAL_MS);

        expect(reload).toHaveBeenCalledTimes(1);
        expect(reload).toHaveBeenCalledWith({ only: ['rows', 'counts'] });
    });

    it('skips the tick while the tab is not visible', () => {
        vi.spyOn(document, 'visibilityState', 'get').mockReturnValue('hidden');
        renderHook(() => useLivePoll(['rows']));

        vi.advanceTimersByTime(POLL_INTERVAL_MS * 3);

        expect(reload).not.toHaveBeenCalled();
    });

    it('stops when the page goes', () => {
        const { unmount } = renderHook(() => useLivePoll(['rows']));
        unmount();

        vi.advanceTimersByTime(POLL_INTERVAL_MS * 2);

        expect(reload).not.toHaveBeenCalled();
    });
});
