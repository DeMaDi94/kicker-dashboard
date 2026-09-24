import { Link, usePage } from '@inertiajs/react';
import AppLogoIcon from '@/components/app-logo-icon';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { useTranslation } from '@/hooks/use-translation';
import { NAV_ITEMS, type ShellNavItem } from '@/layouts/shell/nav-items';
import { UserChip } from '@/layouts/shell/user-chip';
import { cn } from '@/lib/utils';
import { dashboard } from '@/routes';

/*
 * Folding must not move anything that stays visible. Every row keeps the same
 * left padding in both states, so the rail narrows around the icons rather
 * than sliding them into the middle of it — the eye keeps its place and only
 * the labels come and go. That is also what fixes the rail's folded width:
 * the 32 px icon column with the row padding and the rail padding on both
 * sides, 8 + 8 + 32 + 8 + 8. One side short, and an entry's highlight no
 * longer centres on its icon.
 */
const FOLDED_WIDTH = 'compact:w-16';
const OPEN_WIDTH = 'compact:w-[236px]';

/** The rail's own left gutter, shared by the mark, the entries and the chip. */
const ROW_INSET = 'px-2';

/*
 * One icon column for the whole rail. The mark and the user chip's tile are
 * 32 px squares and a lucide glyph is 16, so left-aligning both would put the
 * entry labels 16 px inside the app name and the user's name. Each glyph gets
 * the tile's column and centres in it, and every label in the rail then
 * starts on the same edge — folded, that column is the whole rail.
 */
const ICON_COLUMN = 'flex size-8 flex-none items-center justify-center';

export function isActiveSection(
    item: ShellNavItem,
    currentUrl: string,
): boolean {
    return item.sections.some(
        (section) =>
            currentUrl === section || currentUrl.startsWith(`${section}/`),
    );
}

export function AppSidebar({
    collapsed,
    items = NAV_ITEMS,
}: {
    collapsed: boolean;
    items?: ShellNavItem[];
}) {
    const { name } = usePage().props;
    const { currentUrl } = useCurrentUrl();
    const { t } = useTranslation();

    /* Collapsed only means anything once the rail is a column: below the
       compact breakpoint it is a horizontal bar and everything shows. */
    const whenFolded = (...classes: string[]) =>
        collapsed ? classes.map((each) => `compact:${each}`) : [];

    return (
        /* Below the compact breakpoint the rail is a horizontal bar, so the
           column layout starts at the breakpoint. */
        <aside
            className={cn(
                'flex flex-none items-center gap-1 border-b border-brand-line bg-brand-rail p-2',
                'compact:flex-col compact:items-stretch compact:border-r compact:border-b-0',
                /* The rail keeps its own scroll: a long view moves under it,
                   not with it. */
                'compact:h-full compact:overflow-y-auto',
                collapsed ? FOLDED_WIDTH : OPEN_WIDTH,
            )}
            aria-label={t('Navigation')}
        >
            {/* The mark's row is a fixed height rather than one that follows
                its text, so folding the text away does not re-centre the tile
                and shift everything under it. */}
            <Link
                href={dashboard()}
                className={cn(
                    'flex h-12 flex-none items-center gap-2.5 rounded-brand',
                    ROW_INSET,
                )}
            >
                <AppLogoIcon className="size-8 flex-none" />
                <span
                    className={cn(
                        'hidden min-w-0 truncate text-[13px] font-semibold tracking-[-0.01em] whitespace-nowrap text-brand-ink',
                        'compact:block',
                        ...whenFolded('hidden'),
                    )}
                >
                    {name}
                </span>
            </Link>

            <div className="mx-1 h-5 w-px flex-none bg-brand-line compact:my-1 compact:h-px compact:w-auto" />

            {/* As a horizontal bar the entries keep their labels where they
                fit. Where they do not, they scroll sideways between the mark
                and the chip rather than cutting a label mid-word; below the
                phone breakpoint only the icons are left, each still named by
                its label and its title. */}
            <div
                className={cn(
                    'flex min-w-0 flex-1 [scrollbar-width:thin] items-center gap-1 overflow-x-auto',
                    'compact:flex-none compact:flex-col compact:items-stretch compact:overflow-visible',
                )}
            >
                {/* Folded, the label keeps its box and loses only its ink, so
                    the entries below it do not slide up. `visibility: hidden`
                    takes it out of the accessibility tree as well, and the
                    clip stops the wider word widening the folded rail. */}
                <p
                    className={cn(
                        'hidden overflow-hidden py-1.5 brand-label whitespace-nowrap text-brand-label compact:block',
                        ROW_INSET,
                        collapsed && 'compact:invisible',
                    )}
                >
                    {t('Navigation')}
                </p>

                {items.map((item) => {
                    /* The entry stays active across its whole section. */
                    const active = isActiveSection(item, currentUrl);
                    const title = t(item.title);

                    return (
                        <Link
                            key={item.title}
                            href={item.href}
                            aria-current={active ? 'page' : undefined}
                            title={title}
                            className={cn(
                                'flex h-9 flex-none items-center gap-2.5 overflow-hidden rounded-brand text-sm whitespace-nowrap',
                                ROW_INSET,
                                active
                                    ? 'bg-brand-accent-soft font-semibold text-brand-accent-ink'
                                    : 'font-medium text-brand-ink-soft hover:bg-brand-hover hover:text-brand-ink',
                            )}
                        >
                            <span aria-hidden="true" className={ICON_COLUMN}>
                                <item.icon
                                    className={cn(
                                        'size-4',
                                        active
                                            ? 'text-brand-accent-strong'
                                            : 'text-brand-muted',
                                    )}
                                />
                            </span>
                            <span
                                className={cn(
                                    'max-phone:sr-only',
                                    ...whenFolded('hidden'),
                                )}
                            >
                                {title}
                            </span>
                        </Link>
                    );
                })}
            </div>

            <div className="compact:flex-1" />
            <div className="ml-auto hidden h-px bg-brand-line compact:my-1 compact:block" />

            {/* The user chip closes the rail. */}
            <UserChip collapsed={collapsed} />
        </aside>
    );
}
