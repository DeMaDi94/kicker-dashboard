import type { Page } from '@playwright/test';

/*
 * The seeded development user (database/seeders/DatabaseSeeder.php). The suite
 * signs in through the real login form once per run — see
 * tests/e2e/auth.setup.ts.
 */
export const STORAGE_STATE = 'tests/e2e/.auth/user.json';

export async function login(page: Page): Promise<void> {
    await page.goto('/login');
    await page.locator('#email').fill('test@example.com');
    await page.locator('#password').fill('password');
    /* The remember cookie lets a test open a context with its own Laravel
       session without signing in again. */
    await page.locator('#remember').check();
    await page.locator('[data-test="login-button"]').click();
    // D1 — a sign-in lands on the season view at `/`.
    await page.waitForURL((url) => url.pathname === '/');
}
