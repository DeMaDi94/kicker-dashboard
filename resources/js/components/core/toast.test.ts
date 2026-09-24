import { beforeEach, describe, expect, it, vi } from 'vitest';
import { TOAST_DURATION_MS, toast } from './toast';

const sonner = vi.hoisted(() => vi.fn());

vi.mock('sonner', () => ({ toast: sonner }));

beforeEach(() => {
    sonner.mockReset();
});

describe('toasts', () => {
    it('prefixes the three kinds with ℹ, ✓ and ⚠', () => {
        toast('Saved', 'ok');
        toast('Please check', 'err');
        toast('Notice');

        expect(sonner.mock.calls.map((call) => call[0])).toEqual([
            '✓ Saved',
            '⚠ Please check',
            'ℹ Notice',
        ]);
    });

    it('marks each toast with its kind', () => {
        toast('Saved', 'ok');

        expect(sonner.mock.calls[0]?.[1]).toMatchObject({
            className: 'core-toast core-toast-ok',
        });
    });

    it('dismisses itself after 4.5 s unless told otherwise', () => {
        toast('Saved');

        expect(sonner.mock.calls[0]?.[1]).toMatchObject({
            duration: TOAST_DURATION_MS,
        });
        expect(TOAST_DURATION_MS).toBe(4500);
    });

    it('stays until dismissed when the duration is 0', () => {
        toast('Conflict', 'err', 0);

        expect(sonner.mock.calls[0]?.[1]).toMatchObject({
            duration: Infinity,
        });
    });

    it('never throws, even when the toaster does', () => {
        sonner.mockImplementation(() => {
            throw new Error('no toaster mounted');
        });

        expect(() => toast('Could not save', 'err')).not.toThrow();
    });
});
