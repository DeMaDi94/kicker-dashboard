import { expect, test } from './support/test';

/*
 * MD-05 — the copied kicker league page fills the points fields in addition
 * to typing them (MD-01); nothing is stored until the points are saved.
 */
test('the copied kicker page fills the points, which are then saved', async ({
    page,
}) => {
    const run = Date.now().toString(36);
    const players = [
        { name: `Anna ${run}`, alias: `anna-${run}` },
        { name: `Bert ${run}`, alias: `bert-${run}` },
        { name: `Carl ${run}`, alias: `carl-${run}` },
    ];
    const season = `Kicker ${run}`;

    for (const player of players) {
        await page.goto('/players/create');
        await page.getByLabel('Name', { exact: true }).fill(player.name);
        await page.getByLabel('Alias im kicker Manager').fill(player.alias);
        await page.getByRole('button', { name: 'Mitspieler anlegen' }).click();
        // Three toasts queue, so wait for the list rather than the toast.
        await page.waitForURL(/\/players$/);
    }

    await page.goto('/seasons/create');
    await page.getByLabel('Name', { exact: true }).fill(season);
    await page.getByLabel('Startbetrag (€)').fill('4,50');
    await page.getByLabel('Schrittweite (€)').fill('0,50');
    for (const player of players) {
        await page
            .getByRole('checkbox', { name: new RegExp(player.name) })
            .check();
    }
    await page.getByRole('button', { name: 'Saison anlegen' }).click();
    await expect(page.getByText('Saison angelegt.')).toBeVisible();

    await page.getByRole('link', { name: 'Punkte eintragen' }).click();
    await page.getByLabel(new RegExp(players[2]!.name)).fill('7');

    await page.getByRole('button', { name: 'Aus kicker einfügen' }).click();
    await page
        .getByLabel('Kopierte Liga-Seite')
        .fill(
            [
                'Spieltagswertung',
                'Platz\tName\tPunkte',
                '1\t',
                `${players[0]!.alias}(Admin)`,
                '85',
                '2\t',
                players[1]!.alias,
                '-3',
                '3\t',
                'Fremder',
                '1',
                'Saisonwertung',
            ].join('\n'),
        );
    await page.getByRole('button', { name: 'Übernehmen' }).click();

    await expect(page.getByLabel(new RegExp(players[0]!.name))).toHaveValue(
        '85',
    );
    await expect(page.getByLabel(new RegExp(players[1]!.name))).toHaveValue(
        '-3',
    );
    // The player the text lacks keeps what was typed, and is named.
    await expect(page.getByLabel(new RegExp(players[2]!.name))).toHaveValue(
        '7',
    );
    await expect(page.getByRole('status')).toContainText(
        `Ohne Punkte im Text: ${players[2]!.name}`,
    );
    await expect(page.getByRole('status')).toContainText(
        'Kein Mitspieler mit diesem Alias: Fremder',
    );

    await page.getByRole('button', { name: 'Punkte speichern' }).click();
    await page.waitForURL(/\/seasons\/\d+\?matchday=1$/);
    await expect(
        page
            .locator('section', { hasText: 'Gesamttabelle' })
            .locator('tbody tr')
            .first(),
    ).toContainText(players[0]!.name);
});
