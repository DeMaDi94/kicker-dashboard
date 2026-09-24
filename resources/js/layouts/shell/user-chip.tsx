import type { InertiaLinkProps } from '@inertiajs/react';
import { Link, router, usePage } from '@inertiajs/react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useInitials } from '@/hooks/use-initials';
import { useTranslation } from '@/hooks/use-translation';
import { i18nKey } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import { logout } from '@/routes';
import { edit as editProfile } from '@/routes/profile';

/*
 * The user chip at the foot of the navigation rail, and its menu: who is
 * signed in, their account settings, and signing out.
 */
export type ProfileMenuEntry = {
    /** A translation key. */
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    /** Signing out ends the session, so it is a button, not a link. */
    endsSession?: boolean;
};

export const PROFILE_MENU: ProfileMenuEntry[] = [
    { title: i18nKey('Settings'), href: editProfile() },
    { title: i18nKey('Log out'), href: logout(), endsSession: true },
];

export function UserChip({ collapsed }: { collapsed: boolean }) {
    const { auth } = usePage().props;
    const { t } = useTranslation();
    const initials = useInitials();

    const name = auth.user.name;
    const avatar = auth.user.avatar ?? '';

    /* Folded, the rail is the icon column alone and the two lines of text go.
       Below the compact breakpoint the rail is a horizontal bar and nothing
       folds. */
    const whenFolded = collapsed ? 'compact:hidden' : '';

    return (
        <DropdownMenu>
            <DropdownMenuTrigger
                title={t('Signed in as :name', { name })}
                className={cn(
                    'ml-auto flex h-12 flex-none items-center gap-2.5 rounded-brand px-2 text-left hover:bg-brand-hover',
                    'compact:ml-0',
                )}
                data-test="user-chip"
            >
                {/* The chip wears the mark's own tile — same colour, same
                    square — so the rail opens and closes on one shape. */}
                <span className="flex size-8 flex-none items-center justify-center overflow-hidden rounded-brand bg-brand-primary text-[11px] font-semibold text-white">
                    {avatar === '' ? (
                        initials(name)
                    ) : (
                        <img
                            src={avatar}
                            alt=""
                            className="size-full object-cover"
                        />
                    )}
                </span>
                <span
                    className={cn(
                        'hidden min-w-0 flex-col gap-px',
                        'compact:flex',
                        whenFolded,
                    )}
                >
                    <span className="truncate text-[13px] font-medium whitespace-nowrap text-brand-ink">
                        {name}
                    </span>
                    <span className="truncate text-[11px] whitespace-nowrap text-brand-muted">
                        {auth.user.email}
                    </span>
                </span>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="start" className="min-w-56">
                <DropdownMenuLabel className="font-normal">
                    {t('Signed in as :name', { name })}
                </DropdownMenuLabel>
                <DropdownMenuSeparator />

                {PROFILE_MENU.map((entry) => (
                    <DropdownMenuItem asChild key={entry.title}>
                        <Link
                            href={entry.href}
                            as={entry.endsSession ? 'button' : 'a'}
                            className="w-full cursor-pointer"
                            onClick={
                                entry.endsSession
                                    ? () => router.flushAll()
                                    : undefined
                            }
                            data-test={
                                entry.endsSession ? 'logout-button' : undefined
                            }
                        >
                            {t(entry.title)}
                        </Link>
                    </DropdownMenuItem>
                ))}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
