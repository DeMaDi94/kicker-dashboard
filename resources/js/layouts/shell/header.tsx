import { Link } from '@inertiajs/react';
import { PanelLeftClose, PanelLeftOpen } from 'lucide-react';
import { Fragment } from 'react';
import { useTranslation } from '@/hooks/use-translation';
import type { BreadcrumbItem } from '@/types';

/*
 * The application header, reduced to two things: the control that folds the
 * navigation rail, and the trail that says where in the app this view sits.
 * The mark and the user chip live in the rail itself.
 *
 * The trail is the page's `breadcrumbs`, „Section / View“. The last crumb is
 * where the reader is and is not a link; every crumb before it is.
 */
export function AppHeader({
    breadcrumbs,
    collapsed,
    onToggleNav,
}: {
    breadcrumbs: BreadcrumbItem[];
    collapsed: boolean;
    onToggleNav: () => void;
}) {
    const { t } = useTranslation();
    const crumbTitle = (crumb: BreadcrumbItem) =>
        crumb.verbatim ? crumb.title : t(crumb.title);
    const label = collapsed ? t('Expand sidebar') : t('Collapse sidebar');

    return (
        <header className="flex h-13 flex-none items-center gap-3 border-b border-brand-line px-4">
            {/* The fold has its own labelled control, and its icon says which
                way it goes. Below the compact breakpoint the rail is a
                horizontal bar and there is nothing to fold, so it is not
                shown. */}
            <button
                type="button"
                onClick={onToggleNav}
                title={label}
                aria-label={label}
                aria-expanded={!collapsed}
                className="hidden size-7 flex-none items-center justify-center rounded-brand text-brand-ink-soft outline-none hover:bg-brand-hover hover:text-brand-ink focus-visible:shadow-focus compact:flex"
            >
                {collapsed ? (
                    <PanelLeftOpen className="size-4" />
                ) : (
                    <PanelLeftClose className="size-4" />
                )}
            </button>

            <div
                aria-hidden="true"
                className="hidden h-4.5 w-px flex-none bg-brand-line compact:block"
            />

            <nav
                aria-label={t('Breadcrumb')}
                className="flex min-w-0 items-center gap-2 text-[13.5px]"
            >
                {breadcrumbs.map((crumb, index) => {
                    const last = index === breadcrumbs.length - 1;

                    return (
                        <Fragment key={`${crumb.title}-${index}`}>
                            {index > 0 && (
                                <span
                                    aria-hidden="true"
                                    className="flex-none text-brand-rule"
                                >
                                    /
                                </span>
                            )}
                            {/* The view is where the reader is, so on a narrow
                                bar the sections before it give way first. */}
                            {last ? (
                                <span
                                    aria-current="page"
                                    className="max-w-full flex-none truncate font-medium text-brand-ink"
                                >
                                    {crumbTitle(crumb)}
                                </span>
                            ) : (
                                <Link
                                    href={crumb.href}
                                    className="min-w-0 truncate text-brand-muted hover:text-brand-ink"
                                >
                                    {crumbTitle(crumb)}
                                </Link>
                            )}
                        </Fragment>
                    );
                })}
            </nav>
        </header>
    );
}
