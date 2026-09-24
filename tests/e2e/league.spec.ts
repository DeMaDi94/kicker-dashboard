import { expect, hydrating, test } from './support/test';

/*
 * The league round trip: an admin creates the players and a season
 * (PLY-01, SEA-01, SEA-02), a signed-in user enters a matchday's points
 * (MD-01), and a guest reads the result (ACC-01, MD-03, PEN-01, STD-01).
 * Each run names its own players and season, so leftovers do not collide.
 */
test('an admin sets up a season, points are entered and a guest reads the table', async ({
    page,
    browser,
}) => {
    const run = Date.now().toString(36);
    const players = [`Anna ${run}`, `Bert ${run}`];
    const season = `E2E ${run}`;

    for (const name of players) {
        await page.goto('/players/create');
        await page.getByLabel('Name', { exact: true }).fill(name);
        await page.getByLabel('Alias im kicker Manager').fill(`${name}-alias`);
        await page.getByRole('button', { name: 'Mitspieler anlegen' }).click();
        await expect(page.getByText('Mitspieler angelegt.')).toBeVisible();
    }

    await page.goto('/seasons/create');
    await page.getByLabel('Name', { exact: true }).fill(season);
    await page.getByLabel('Startbetrag (€)').fill('4,50');
    await page.getByLabel('Schrittweite (€)').fill('0,50');
    for (const name of players) {
        await page.getByRole('checkbox', { name: new RegExp(name) }).check();
    }
    await page.getByRole('button', { name: 'Saison anlegen' }).click();
    await expect(page.getByText('Saison angelegt.')).toBeVisible();
    await expect(
        page.getByRole('heading', { name: `Saison ${season}` }),
    ).toBeVisible();

    await page.getByRole('link', { name: 'Punkte eintragen' }).click();
    await page.getByLabel(new RegExp(players[0])).fill('40');
    await page.getByLabel(new RegExp(players[1])).fill('55');
    await page.getByRole('button', { name: 'Punkte speichern' }).click();
    /* Saving leads back to the season at this matchday. (Its toast may queue
       behind the ones this run already raised.) */
    await page.waitForURL(/\/seasons\/\d+\?matchday=1$/);

    const guest = hydrating(
        await browser.newPage({ storageState: { cookies: [], origins: [] } }),
    );
    await guest.goto(page.url().replace(/\?.*$/, ''));

    // PEN-01 — 55 points pay one step less than the lowest score's 4,50 €.
    const rows = guest
        .locator('section', { hasText: 'Gesamttabelle' })
        .locator('tbody tr');
    await expect(rows.first()).toContainText(players[1]);
    await expect(rows.first()).toContainText('4,00');
    await expect(rows.nth(1)).toContainText(players[0]);
    await expect(rows.nth(1)).toContainText('4,50');
    await expect(guest.getByRole('link', { name: 'Anmelden' })).toBeVisible();
});
