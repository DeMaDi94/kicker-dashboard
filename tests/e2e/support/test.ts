import { test as base, type Page } from '@playwright/test';

export { expect } from '@playwright/test';
export type { Browser, Locator, Page, Response } from '@playwright/test';

/*
 * Every page arrives server-rendered (config/inertia.php `ssr.enabled`), so its
 * fields and buttons are in the DOM before React has hydrated them. A fill or
 * a click in that gap lands on inert markup and is lost — the search that
 * never filters, the modal that never opens, the autosave that never fires —
 * and the gap widens with every parallel worker loading the bundle. A
 * navigation is therefore not done until React has taken over `#app`.
 */
export async function untilHydrated(page: Page): Promise<void> {
    await page.waitForFunction(() => {
        const root = document.getElementById('app');
        const owned = (node: Element) =>
            Object.keys(node).some((key) => key.startsWith('__reactFiber$'));

        /* React 19 hoists resource hints (`<link rel="preload">` for an
           image) to the top of the markup, and those are never hydrated —
           so any rendered child counts, not only the first. */
        return (
            root !== null &&
            Object.keys(root).some((key) =>
                key.startsWith('__reactContainer$'),
            ) &&
            Array.from(root.children).some(owned)
        );
    });
}

/** Make `goto` and `reload` on this page wait for hydration. */
export function hydrating(page: Page): Page {
    const goto = page.goto.bind(page);
    const reload = page.reload.bind(page);

    page.goto = async (...args) => {
        const response = await goto(...args);
        await untilHydrated(page);

        return response;
    };
    page.reload = async (...args) => {
        const response = await reload(...args);
        await untilHydrated(page);

        return response;
    };

    return page;
}

export const test = base.extend({
    page: async ({ page }, use) => {
        await use(hydrating(page));
    },
});
