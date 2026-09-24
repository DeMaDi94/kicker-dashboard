import { Link, router } from '@inertiajs/react';
import { ArrowDown, ArrowUp } from 'lucide-react';
import type { ReactNode } from 'react';
import { Button } from '@/components/ui/button';
import { useTranslation } from '@/hooks/use-translation';
import { edit, restore } from '@/routes/users';
import { roleLabel } from './role-label';
import type { UserRow } from './types';

type Column = 'name' | 'email' | 'role' | 'verified';

/*
 * B16 — the user list: name, email, role and verification, every column
 * sortable from its header. A deleted user is restored from here; an active
 * one is opened for editing.
 */
export function UserTable({
    users,
    sort,
    onSort,
}: {
    users: UserRow[];
    sort: string;
    onSort: (sort: string) => void;
}) {
    const { t, locale } = useTranslation();
    const dates = new Intl.DateTimeFormat(locale, { dateStyle: 'medium' });

    const header = (column: Column, label: string): ReactNode => {
        const direction =
            sort === column ? 'asc' : sort === `-${column}` ? 'desc' : null;

        return (
            <th
                scope="col"
                aria-sort={
                    direction === 'asc'
                        ? 'ascending'
                        : direction === 'desc'
                          ? 'descending'
                          : 'none'
                }
                className="px-3 py-2 text-left font-medium"
            >
                <button
                    type="button"
                    onClick={() =>
                        onSort(direction === 'asc' ? `-${column}` : column)
                    }
                    className="inline-flex items-center gap-1 rounded-brand outline-none hover:text-brand-ink focus-visible:shadow-focus"
                >
                    {label}
                    {direction === 'asc' && (
                        <ArrowUp className="size-3.5" aria-hidden />
                    )}
                    {direction === 'desc' && (
                        <ArrowDown className="size-3.5" aria-hidden />
                    )}
                </button>
            </th>
        );
    };

    if (users.length === 0) {
        return (
            <p className="px-4 py-8 text-center text-sm text-brand-muted">
                {t('No users found.')}
            </p>
        );
    }

    return (
        /* `relative` keeps absolutely placed descendants (the sr-only labels)
           inside the scrolling box, so they do not widen the page. */
        <div className="relative overflow-x-auto">
            <table className="w-full text-sm">
                <thead className="border-b border-brand-line-soft text-brand-muted">
                    <tr>
                        {header('name', t('Name'))}
                        {header('email', t('Email address'))}
                        {header('role', t('Role'))}
                        {header('verified', t('Email verified'))}
                        <th scope="col" className="px-3 py-2">
                            <span className="sr-only">{t('Actions')}</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {users.map((user) => (
                        <tr
                            key={user.id}
                            className="border-b border-brand-line-faint last:border-0"
                        >
                            <td className="px-3 py-2 font-medium text-brand-ink">
                                {user.name}
                            </td>
                            <td className="px-3 py-2 text-brand-ink-soft">
                                {user.email}
                            </td>
                            <td className="px-3 py-2">
                                {roleLabel(user.role, t)}
                            </td>
                            <td className="px-3 py-2 text-brand-ink-soft">
                                {user.emailVerifiedAt === null
                                    ? t('Not verified')
                                    : dates.format(
                                          new Date(user.emailVerifiedAt),
                                      )}
                            </td>
                            <td className="px-3 py-2 text-right">
                                {user.deleted ? (
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() =>
                                            router.post(
                                                restore(user.id),
                                                {},
                                                { preserveScroll: true },
                                            )
                                        }
                                    >
                                        {t('Restore')}
                                    </Button>
                                ) : (
                                    <Button variant="outline" size="sm" asChild>
                                        <Link href={edit(user.id)}>
                                            {t('Edit')}
                                        </Link>
                                    </Button>
                                )}
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
