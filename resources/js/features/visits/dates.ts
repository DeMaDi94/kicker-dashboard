/*
 * The labels the visit graphs put on their axes, in the active locale. The
 * dates arrive as `YYYY-MM-DD` of Europe/Berlin (VIS-04) and are shown as
 * that calendar day, so they are read as UTC and formatted as UTC.
 */

const asUtc = (date: string): Date => new Date(`${date}T00:00:00Z`);

/** A day of the period, short: „25.09.“ */
export const shortDate = (date: string, locale: string): string =>
    new Intl.DateTimeFormat(locale, {
        day: '2-digit',
        month: '2-digit',
        timeZone: 'UTC',
    }).format(asUtc(date));

/** A day of the period, in full: „Freitag, 25. September 2026“ */
export const longDate = (date: string, locale: string): string =>
    new Intl.DateTimeFormat(locale, {
        dateStyle: 'full',
        timeZone: 'UTC',
    }).format(asUtc(date));

/** VIS-06 — Monday to Sunday, short. 2024-01-01 was a Monday. */
export const weekdays = (locale: string): string[] =>
    Array.from({ length: 7 }, (_, index) =>
        new Intl.DateTimeFormat(locale, {
            weekday: 'short',
            timeZone: 'UTC',
        }).format(new Date(Date.UTC(2024, 0, 1 + index))),
    );
