import { expect, test } from './support/test';

/*
 * Proves the browser harness end to end: Laravel serves, Vite assets resolve,
 * Inertia mounts React. The real journeys arrive with the screens they cover.
 */
test('the application boots and Inertia mounts', async ({ page }) => {
    const response = await page.goto('/');

    expect(response?.status()).toBe(200);
    await expect(page.locator('#app')).toBeAttached();
});

test('a signed-in user reaches the season view', async ({ page }) => {
    await page.goto('/');

    await expect(page.getByRole('main')).toBeVisible();
});

// Signed out: the suite otherwise carries the shared session of
// tests/e2e/auth.setup.ts, and /login then redirects home.
test.describe('signed out', () => {
    test.use({ storageState: { cookies: [], origins: [] } });

    test('the login screen is reachable', async ({ page }) => {
        await page.goto('/login');

        await expect(page.locator('input[type="password"]')).toBeVisible();
    });
});
