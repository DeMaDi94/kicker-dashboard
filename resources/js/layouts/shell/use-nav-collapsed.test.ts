import { act, renderHook } from '@testing-library/react';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { NAV_COLLAPSED_COOKIE, useNavCollapsed } from './use-nav-collapsed';

const page = vi.hoisted(() => ({ collapsed: false }));

vi.mock('@inertiajs/react', () => ({
    usePage: () => ({ props: { navCollapsed: page.collapsed } }),
}));

beforeEach(() => {
    page.collapsed = false;
    document.cookie = `${NAV_COLLAPSED_COOKIE}=; max-age=0; path=/`;
});

describe('the rail remembers that it was folded', () => {
    it('writes the choice to the cookie the server reads back', () => {
        const { result } = renderHook(() => useNavCollapsed());

        expect(result.current.collapsed).toBe(false);

        act(() => result.current.toggle());

        expect(result.current.collapsed).toBe(true);
        expect(document.cookie).toContain(`${NAV_COLLAPSED_COOKIE}=1`);

        act(() => result.current.toggle());

        expect(result.current.collapsed).toBe(false);
        expect(document.cookie).toContain(`${NAV_COLLAPSED_COOKIE}=0`);
    });

    it('starts folded when that is what the server read back', () => {
        page.collapsed = true;

        const { result } = renderHook(() => useNavCollapsed());

        expect(result.current.collapsed).toBe(true);
    });
});
