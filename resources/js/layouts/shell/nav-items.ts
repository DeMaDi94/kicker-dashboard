import type { InertiaLinkProps } from '@inertiajs/react';
import type { LucideIcon } from 'lucide-react';
import { ChartLine, Medal, Trophy, Users } from 'lucide-react';
import { i18nKey } from '@/lib/i18n';
import { home, records } from '@/routes';
import { index as players } from '@/routes/players';
import { index as visits } from '@/routes/visits';

/**
 * One entry of the navigation rail. `title` is a translation key, rendered
 * with `t()` by the rail.
 */
export type ShellNavItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon: LucideIcon;
    /** The URL spaces the entry owns: it stays active for every page in them. */
    sections: string[];
    /** B13 — shown only to a user holding this permission. */
    permission?: string;
};

/*
 * The rail's entries, in order. A product adds its areas here, in the order
 * its navigation requirement states.
 */
export const NAV_ITEMS: ShellNavItem[] = [
    {
        title: i18nKey('Seasons'),
        href: home(),
        icon: Trophy,
        sections: ['/', '/seasons'],
    },
    {
        // STAT-10
        title: i18nKey('Records'),
        href: records(),
        icon: Medal,
        sections: ['/records'],
    },
    {
        title: i18nKey('Players'),
        href: players(),
        icon: Users,
        sections: ['/players'],
        // ACC-03
        permission: 'players.create',
    },
    {
        // VIS-04, D14 — the last entry, for admins only.
        title: i18nKey('Visitors'),
        href: visits(),
        icon: ChartLine,
        sections: ['/visits'],
        permission: 'visits.view',
    },
];
