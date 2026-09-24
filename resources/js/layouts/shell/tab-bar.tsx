import { Link } from '@inertiajs/react';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { useTranslation } from '@/hooks/use-translation';
import { cn, toUrl } from '@/lib/utils';
import type { TabItem } from '@/types';

/*
 * The one tab that is current: the one whose screen is the longest match for
 * the path, a match being the screen itself or a path below it by whole
 * segments. A plain prefix test would light an overview tab (`/projects/7`)
 * on every other screen of that project as well.
 */
export function currentTab(
    items: TabItem[],
    path: string,
): TabItem | undefined {
    let current: TabItem | undefined;
    let longest = -1;

    for (const item of items) {
        if (item.href === undefined) {
            continue;
        }

        for (const href of [item.href, ...(item.covers ?? [])]) {
            const screen = new URL(toUrl(href), 'http://localhost').pathname;
            const matches = path === screen || path.startsWith(`${screen}/`);

            if (matches && screen.length > longest) {
                current = item;
                longest = screen.length;
            }
        }
    }

    return current;
}

/*
 * A strip of tabs under the header, for a group of related screens. With
 * `secondary` it renders as the second level under a main strip: the same
 * order and behaviour, one step quieter.
 *
 * Below the compact breakpoint the bar scrolls sideways instead of wrapping,
 * so the tabs keep their order and their height.
 */
export function TabBar({
    items,
    label,
    secondary = false,
}: {
    items: TabItem[];
    label: string;
    secondary?: boolean;
}) {
    const { t } = useTranslation();
    const current = currentTab(items, useCurrentUrl().currentUrl);

    return (
        <nav
            aria-label={t(label)}
            className={cn(
                'sticky top-0 z-30 flex flex-none [scrollbar-width:thin] gap-0.5 overflow-x-auto border-b border-brand-line px-4',
                secondary ? 'bg-brand-bg' : 'bg-brand-card',
            )}
        >
            {items.map((item) => {
                /* A tab whose screen is not built yet is shown, but is not a
                   link and cannot be current. */
                if (item.href === undefined) {
                    return (
                        <span
                            key={item.title}
                            aria-disabled="true"
                            className={cn(
                                'flex-none border-b-2 border-transparent px-3 whitespace-nowrap text-brand-faint',
                                secondary
                                    ? 'pt-2.5 pb-2 text-[13px]'
                                    : 'pt-3.5 pb-2.5 text-[13.5px]',
                            )}
                        >
                            {t(item.title)}
                        </span>
                    );
                }

                const active = item === current;

                return (
                    <Link
                        key={item.title}
                        href={item.href}
                        aria-current={active ? 'page' : undefined}
                        className={cn(
                            'flex-none border-b-2 px-3 whitespace-nowrap',
                            secondary
                                ? 'pt-2.5 pb-2 text-[13px]'
                                : 'pt-3.5 pb-2.5 text-[13.5px]',
                            active
                                ? 'border-brand-ink font-semibold text-brand-ink'
                                : 'border-transparent text-brand-muted hover:text-brand-ink',
                        )}
                    >
                        {t(item.title)}
                    </Link>
                );
            })}
        </nav>
    );
}
