import type { InertiaLinkProps } from '@inertiajs/react';
import type { ReactNode } from 'react';
import type { BreadcrumbItem } from '@/types/navigation';

/**
 * One tab. `title` is a translation key. A tab whose screen is not built yet
 * carries no `href` and is shown as present but unavailable, rather than being
 * left out of the strip.
 */
export type TabItem = {
    title: string;
    href?: NonNullable<InertiaLinkProps['href']>;
    /*
     * The other screens this tab stands for: a tab that links to the first of
     * several screens stays current on each of them.
     */
    covers?: NonNullable<InertiaLinkProps['href']>[];
};

/** A tab bar; `label` is a translation key, the bar's accessible name. */
export type TabGroup = {
    label: string;
    items: TabItem[];
};

export type AppLayoutProps = {
    children: ReactNode;
    /** The header trail; the last crumb is the view, and its title names the view in a failure toast. */
    breadcrumbs?: BreadcrumbItem[];
    tabs?: TabGroup;
    /** A second strip under `tabs`, for a group with two levels. */
    subTabs?: TabGroup;
};

export type FlashToast = {
    type: 'success' | 'info' | 'warning' | 'error';
    message: string;
};

export type AuthLayoutProps = {
    children?: ReactNode;
    name?: string;
    title?: string;
    description?: string;
};
