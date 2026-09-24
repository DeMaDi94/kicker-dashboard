/**
 * An amount held in cents, in euros in the active locale — the league's
 * penalties are in € (PEN-01).
 */
export const formatCents = (cents: number, locale: string): string =>
    new Intl.NumberFormat(locale, {
        style: 'currency',
        currency: 'EUR',
    }).format(cents / 100);
