import { expect, test } from './support/test';

/*
 * D7 — every screen works on a phone: nothing scrolls sideways down to a
 * 320 px viewport. The pages run against whatever data the database holds.
 */
const PAGES = [
    '/',
    '/players',
    '/players/create',
    '/seasons/create',
    '/settings/profile',
    '/settings/users',
];

test.use({ viewport: { width: 320, height: 640 } });

for (const path of PAGES) {
    test(`${path} fits a 320 px phone`, async ({ page }) => {
        await page.goto(path);

        const overflow = await page.evaluate(
            () => document.documentElement.scrollWidth - window.innerWidth,
        );

        expect(overflow).toBeLessThanOrEqual(0);
    });
}

test.describe('signed out', () => {
    test.use({ storageState: { cookies: [], origins: [] } });

    for (const path of ['/', '/login', '/records']) {
        test(`${path} fits a 320 px phone`, async ({ page }) => {
            await page.goto(path);

            const overflow = await page.evaluate(
                () => document.documentElement.scrollWidth - window.innerWidth,
            );

            expect(overflow).toBeLessThanOrEqual(0);
        });
    }
});
