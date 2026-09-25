/*
 * Points in time the server sends as ISO 8601, shown in German time
 * (Europe/Berlin, as VIS-04 and LOG-03 state), whatever the browser's zone.
 */

const ZONE = 'Europe/Berlin';

/** „25.09.2026“ */
export const formatDate = (iso: string, locale: string): string =>
    new Intl.DateTimeFormat(locale, {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        timeZone: ZONE,
    }).format(new Date(iso));

/** „25.09.2026, 14:05“ */
export const formatDateTime = (iso: string, locale: string): string =>
    new Intl.DateTimeFormat(locale, {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        timeZone: ZONE,
    }).format(new Date(iso));
