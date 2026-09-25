import { Head, Link, usePage } from '@inertiajs/react';
import { PageTitle } from '@/components/core/page-title';
import { Panel } from '@/components/core/panel';
import { Button } from '@/components/ui/button';
import { useTranslation } from '@/hooks/use-translation';
import { i18nKey } from '@/lib/i18n';
import { create, edit, index } from '@/routes/players';

type PlayersIndexProps = {
    players: { id: number; name: string; alias: string }[];
};

/*
 * PLY-01 — the league's players with their kicker Manager alias. ACC-03 —
 * created by admins only, never deleted; PLY-02 — an admin edits one.
 */
export default function PlayersIndex({ players }: PlayersIndexProps) {
    const { t } = useTranslation();
    const { auth } = usePage().props;
    const canEdit = auth.permissions.includes('players.update');

    return (
        <>
            <Head title={t('Players')} />

            <PageTitle title={t('Players')}>
                <Button asChild>
                    <Link href={create()}>{t('Create player')}</Link>
                </Button>
            </PageTitle>

            <Panel>
                {players.length === 0 ? (
                    <p className="px-4 py-8 text-center text-sm text-brand-muted">
                        {t('No players yet. Create them first.')}
                    </p>
                ) : (
                    <table className="w-full text-sm">
                        <thead className="border-b border-brand-line-soft text-brand-muted">
                            <tr>
                                <th
                                    scope="col"
                                    className="px-2 py-2 text-left font-medium phone:px-3"
                                >
                                    {t('Name')}
                                </th>
                                <th
                                    scope="col"
                                    className="px-2 py-2 text-left font-medium phone:px-3"
                                >
                                    {t('Alias')}
                                </th>
                                {canEdit && (
                                    <th scope="col">
                                        <span className="sr-only">
                                            {t('Actions')}
                                        </span>
                                    </th>
                                )}
                            </tr>
                        </thead>
                        <tbody>
                            {players.map((player) => (
                                <tr
                                    key={player.id}
                                    className="border-b border-brand-line-soft last:border-0"
                                >
                                    <td className="px-2 py-2 font-medium phone:px-3">
                                        {player.name}
                                    </td>
                                    <td className="px-2 py-2 text-brand-muted phone:px-3">
                                        {player.alias}
                                    </td>
                                    {canEdit && (
                                        <td className="px-2 py-1 text-right phone:px-3">
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                asChild
                                            >
                                                <Link
                                                    href={edit(player.id)}
                                                    aria-label={t(
                                                        'Edit :name',
                                                        { name: player.name },
                                                    )}
                                                >
                                                    {t('Edit')}
                                                </Link>
                                            </Button>
                                        </td>
                                    )}
                                </tr>
                            ))}
                        </tbody>
                    </table>
                )}
            </Panel>
        </>
    );
}

PlayersIndex.layout = {
    breadcrumbs: [{ title: i18nKey('Players'), href: index() }],
};
