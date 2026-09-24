import { Head, router } from '@inertiajs/react';
import { PageTitle } from '@/components/core/page-title';
import { Panel } from '@/components/core/panel';
import { Button } from '@/components/ui/button';
import type { SeasonOption } from '@/features/seasons/types';
import { useTranslation } from '@/hooks/use-translation';
import { i18nKey } from '@/lib/i18n';
import { home } from '@/routes';
import { deleted, restore } from '@/routes/seasons';

type DeletedSeasonsProps = {
    seasons: SeasonOption[];
};

/*
 * SEA-06 — the deleted seasons, each restorable by an admin with its points.
 */
export default function DeletedSeasons({ seasons }: DeletedSeasonsProps) {
    const { t } = useTranslation();

    return (
        <>
            <Head title={t('Deleted seasons')} />

            <PageTitle title={t('Deleted seasons')} />

            <Panel>
                {seasons.length === 0 ? (
                    <p className="px-4 py-8 text-center text-sm text-brand-muted">
                        {t('There is no deleted season.')}
                    </p>
                ) : (
                    <table className="w-full text-sm">
                        <thead className="border-b border-brand-line-soft text-brand-muted">
                            <tr>
                                <th
                                    scope="col"
                                    className="px-2 py-2 text-left font-medium phone:px-3"
                                >
                                    {t('Season')}
                                </th>
                                <th
                                    scope="col"
                                    className="px-2 py-2 phone:px-3"
                                >
                                    <span className="sr-only">
                                        {t('Actions')}
                                    </span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {seasons.map((season) => (
                                <tr
                                    key={season.id}
                                    className="border-b border-brand-line-soft last:border-0"
                                >
                                    <td className="px-2 py-2 font-medium phone:px-3">
                                        {season.name}
                                    </td>
                                    <td className="px-2 py-2 text-right phone:px-3">
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() =>
                                                router.post(
                                                    restore(season.id),
                                                    {},
                                                    { preserveScroll: true },
                                                )
                                            }
                                        >
                                            {t('Restore')}
                                        </Button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                )}
            </Panel>
        </>
    );
}

DeletedSeasons.layout = {
    breadcrumbs: [
        { title: i18nKey('Seasons'), href: home() },
        { title: i18nKey('Deleted seasons'), href: deleted() },
    ],
};
