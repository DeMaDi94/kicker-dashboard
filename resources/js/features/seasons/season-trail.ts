import { i18nKey } from '@/lib/i18n';
import { home } from '@/routes';
import { show } from '@/routes/seasons';
import type { BreadcrumbItem } from '@/types';

/** „Seasons / {season}“ — the head of the trail of every view inside a season. */
export function seasonTrail(season: {
    id: number;
    name: string;
}): BreadcrumbItem[] {
    return [
        { title: i18nKey('Seasons'), href: home() },
        { title: season.name, href: show(season.id), verbatim: true },
    ];
}
