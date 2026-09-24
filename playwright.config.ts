import { defineConfig, devices } from '@playwright/test';
import { STORAGE_STATE } from './tests/e2e/support/auth';

/*
 * End-to-end tests. Chromium only: one reference browser keeps the suite fast
 * and its failures unambiguous; add a project here when a requirement names
 * another browser.
 *
 * The browser runs in the app's default locale and timezone
 * (config/app.php), so locators match the text a user of that locale sees.
 */
export default defineConfig({
    testDir: './tests/e2e',
    fullyParallel: true,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 2 : 0,
    workers: process.env.CI ? 1 : undefined,
    reporter: process.env.CI ? 'github' : 'list',

    use: {
        baseURL: process.env.APP_URL ?? 'http://127.0.0.1:8000',
        trace: 'on-first-retry',
        locale: 'de-DE',
        timezoneId: 'Europe/Berlin',
    },

    projects: [
        // The suite signs in once and reuses the session; the login route is
        // throttled, so one login per test would lock the run out.
        {
            name: 'setup',
            testMatch: /auth\.setup\.ts/,
        },
        {
            name: 'chromium',
            use: { ...devices['Desktop Chrome'], storageState: STORAGE_STATE },
            dependencies: ['setup'],
        },
    ],

    webServer: {
        /* `artisan serve` drops every variable in $_ENV from the server it
           starts; with PHP's `variables_order` EGPCS that includes any env
           passed here, so it is kept out of $_ENV. */
        command:
            'php -d variables_order=GPCS artisan serve --host=127.0.0.1 --port=8000',
        url: 'http://127.0.0.1:8000',
        reuseExistingServer: !process.env.CI,
        timeout: 60_000,
    },
});
