import type { InertiaLinkProps } from '@inertiajs/react';
import type { LucideIcon } from 'lucide-react';
import { LayoutGrid } from 'lucide-react';
import { i18nKey } from '@/lib/i18n';
import { dashboard } from '@/routes';

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
};

/*
 * The rail's entries, in order. A product adds its areas here, in the order
 * its navigation requirement states.
 */
export const NAV_ITEMS: ShellNavItem[] = [
    {
        title: i18nKey('Dashboard'),
        href: dashboard(),
        icon: LayoutGrid,
        sections: ['/dashboard'],
    },
];
