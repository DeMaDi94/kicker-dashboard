import { expect, test } from './support/test';

/*
 * B14–B16 — an admin's round trip through user management. The suite signs
 * in as the seeded admin (tests/e2e/auth.setup.ts). Each run creates its own
 * user, so a leftover row from an earlier run does not collide.
 */
test('an admin creates, edits, deletes and restores a user', async ({
    page,
}) => {
    const email = `e2e-${Date.now()}@example.com`;

    await page.goto('/settings/profile');
    await page
        .getByRole('navigation', { name: 'Einstellungen' })
        .getByRole('link', { name: 'Benutzer' })
        .click();
    await expect(
        page.getByRole('heading', { name: 'Benutzer', exact: true }),
    ).toBeVisible();

    await page.getByRole('link', { name: 'Benutzer anlegen' }).click();
    await page.getByLabel('Name', { exact: true }).fill('E2E Person');
    await page.getByLabel('E-Mail-Adresse', { exact: true }).fill(email);
    await page.getByRole('combobox', { name: 'Rolle' }).click();
    await page.getByRole('option', { name: 'Benutzer' }).click();
    await page.getByRole('button', { name: 'Benutzer anlegen' }).click();

    await expect(
        page.getByText('Benutzer angelegt. Die Einladung ist unterwegs.'),
    ).toBeVisible();

    await page.getByRole('searchbox').fill(email);
    const row = page.locator('tbody tr', { hasText: email });
    await expect(row).toHaveCount(1);
    await expect(page.locator('tbody tr')).toHaveCount(1);
    await expect(row).toContainText('Nicht bestätigt');

    await row.getByRole('link', { name: 'Bearbeiten' }).click();
    await page.getByLabel('Name', { exact: true }).fill('E2E Renamed');
    await page.getByRole('button', { name: 'Speichern' }).click();
    await expect(page.getByText('Benutzer gespeichert.')).toBeVisible();

    await page.getByRole('button', { name: 'Benutzer löschen' }).click();
    await page
        .getByRole('dialog')
        .getByRole('button', { name: 'Benutzer löschen' })
        .click();
    await expect(page.getByText('Benutzer gelöscht.')).toBeVisible();

    await page.getByRole('combobox', { name: 'Status' }).click();
    await page.getByRole('option', { name: 'Gelöschte Benutzer' }).click();
    await page.getByRole('searchbox').fill(email);
    const deleted = page.locator('tbody tr', { hasText: email });
    await expect(deleted).toContainText('E2E Renamed');

    await deleted.getByRole('button', { name: 'Wiederherstellen' }).click();
    await expect(page.getByText('Benutzer wiederhergestellt.')).toBeVisible();
    await expect(page.locator('tbody tr', { hasText: email })).toHaveCount(0);
});
