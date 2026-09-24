import { Head, Link } from '@inertiajs/react';
import Heading from '@/components/heading';
import { ListPager } from '@/components/core/list-pager';
import { Panel } from '@/components/core/panel';
import { useListQuery } from '@/components/core/use-list-query';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { roleLabel } from '@/features/users/role-label';
import type { Role, UserListFilters, UserRow } from '@/features/users/types';
import { UserTable } from '@/features/users/user-table';
import { useTranslation } from '@/hooks/use-translation';
import { i18nKey } from '@/lib/i18n';
import { create, index } from '@/routes/users';

type UsersIndexProps = {
    users: UserRow[];
    pagination: { page: number; lastPage: number; total: number };
    filters: UserListFilters;
    roles: Role[];
};

/* A select cannot hold an empty value; this stands for "no filter". */
const ALL = 'all';

export default function UsersIndex({
    users,
    pagination,
    filters,
    roles,
}: UsersIndexProps) {
    const { t } = useTranslation();
    const { values, setFilter, typeFilter, setPage } = useListQuery(filters, [
        'users',
        'pagination',
        'filters',
    ]);

    return (
        <>
            <Head title={t('Users')} />

            <div className="space-y-4">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <Heading
                        variant="small"
                        title={t('Users')}
                        description={t(
                            'Create, edit and delete the accounts of this app',
                        )}
                    />
                    <Button asChild>
                        <Link href={create()}>{t('Create user')}</Link>
                    </Button>
                </div>

                <div className="flex flex-wrap gap-2">
                    <Input
                        type="search"
                        className="min-w-48 flex-1"
                        value={values.search}
                        onChange={(event) =>
                            typeFilter('search', event.target.value)
                        }
                        placeholder={t('Search name or email')}
                        aria-label={t('Search name or email')}
                    />

                    <Select
                        value={values.role === '' ? ALL : values.role}
                        onValueChange={(value) =>
                            setFilter('role', value === ALL ? '' : value)
                        }
                    >
                        <SelectTrigger className="w-44" aria-label={t('Role')}>
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={ALL}>
                                {t('All roles')}
                            </SelectItem>
                            {roles.map((role) => (
                                <SelectItem key={role} value={role}>
                                    {roleLabel(role, t)}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>

                    <Select
                        value={values.status === '' ? ALL : values.status}
                        onValueChange={(value) =>
                            setFilter('status', value === ALL ? '' : value)
                        }
                    >
                        <SelectTrigger
                            className="w-44"
                            aria-label={t('Status')}
                        >
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={ALL}>
                                {t('Active users')}
                            </SelectItem>
                            <SelectItem value="deleted">
                                {t('Deleted users')}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <Panel>
                    <UserTable
                        users={users}
                        sort={values.sort}
                        onSort={(sort) => setFilter('sort', sort)}
                    />
                </Panel>

                {pagination.lastPage > 1 && (
                    <ListPager
                        page={pagination.page}
                        lastPage={pagination.lastPage}
                        onPageChange={setPage}
                    />
                )}
            </div>
        </>
    );
}

UsersIndex.layout = {
    wide: true,
    breadcrumbs: [{ title: i18nKey('Users'), href: index() }],
};
