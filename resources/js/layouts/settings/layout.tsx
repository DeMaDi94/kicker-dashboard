import { Link, usePage } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';
import { PageTitle } from '@/components/core/page-title';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { useTranslation } from '@/hooks/use-translation';
import { i18nKey } from '@/lib/i18n';
import { cn, toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import { index as users } from '@/routes/users';
import type { NavItem } from '@/types';

const accountNavItems: NavItem[] = [
    {
        title: i18nKey('Profile'),
        href: edit(),
        icon: null,
    },
    {
        title: i18nKey('Security'),
        href: editSecurity(),
        icon: null,
    },
    {
        title: i18nKey('Appearance'),
        href: editAppearance(),
        icon: null,
    },
];

/* B13 — shown only to whoever may see the user list. */
const usersNavItem: NavItem = {
    title: i18nKey('Users'),
    href: users(),
    icon: null,
};

/*
 * The account settings: a title, a short list of sections beside the form —
 * marked the way the navigation rail marks its entries — and the form itself
 * at a readable width. A list (`wide`) takes the whole row instead.
 */
export default function SettingsLayout({
    children,
    wide = false,
}: PropsWithChildren<{ wide?: boolean }>) {
    const { isCurrentOrParentUrl } = useCurrentUrl();
    const { t } = useTranslation();
    const { auth } = usePage().props;
    const sidebarNavItems = auth.permissions.includes('users.view')
        ? [...accountNavItems, usersNavItem]
        : accountNavItems;

    return (
        <>
            <PageTitle
                title={t('Settings')}
                description={t('Manage your profile and account settings')}
            />

            <div className="flex flex-col gap-6 lg:flex-row lg:gap-12">
                <aside className="w-full lg:w-48">
                    <nav
                        className="flex gap-1 overflow-x-auto lg:flex-col"
                        aria-label={t('Settings')}
                    >
                        {sidebarNavItems.map((item, index) => {
                            const active = isCurrentOrParentUrl(item.href);

                            return (
                                <Link
                                    key={`${toUrl(item.href)}-${index}`}
                                    href={item.href}
                                    aria-current={active ? 'page' : undefined}
                                    className={cn(
                                        'flex h-9 flex-none items-center rounded-brand px-3 text-sm whitespace-nowrap outline-none focus-visible:shadow-focus',
                                        active
                                            ? 'bg-brand-accent-soft font-semibold text-brand-accent-ink'
                                            : 'font-medium text-brand-ink-soft hover:bg-brand-hover hover:text-brand-ink',
                                    )}
                                >
                                    {t(item.title)}
                                </Link>
                            );
                        })}
                    </nav>
                </aside>

                <div className={cn('min-w-0 flex-1', !wide && 'md:max-w-2xl')}>
                    <section className={cn('space-y-12', !wide && 'max-w-xl')}>
                        {children}
                    </section>
                </div>
            </div>
        </>
    );
}
