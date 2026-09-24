import { test as setup } from './support/test';
import { login, STORAGE_STATE } from './support/auth';

/*
 * Signing in once per run rather than per test: the login route is throttled
 * (`throttle:6,1`), so a suite that logs in for every test locks itself out.
 */
setup('sign in', async ({ page }) => {
    await login(page);
    await page.context().storageState({ path: STORAGE_STATE });
});
