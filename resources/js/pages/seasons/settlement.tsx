import { Head, Link, useForm } from '@inertiajs/react';
import UpdateSeasonSettlementController from '@/actions/App/Http/Seasons/UpdateSeasonSettlement/UpdateSeasonSettlementController';
import { PageTitle } from '@/components/core/page-title';
import { Panel, PanelBody } from '@/components/core/panel';
import { Button } from '@/components/ui/button';
import { SettlementSelect } from '@/features/seasons/settlement-select';
import { useTranslation } from '@/hooks/use-translation';
import { i18nKey } from '@/lib/i18n';
import { home } from '@/routes';
import { show } from '@/routes/seasons';

type SeasonSettlementProps = {
    season: { id: number; name: string; settlementMatchday: number | null };
    matchdays: number[];
};

/*
 * PEN-04 — change or remove the interim settlement; the one season setting
 * that changes after the season was created (ACC-03).
 */
export default function SeasonSettlement({
    season,
    matchdays,
}: SeasonSettlementProps) {
    const { t } = useTranslation();
    const form = useForm<{ settlement_matchday: number | null }>({
        settlement_matchday: season.settlementMatchday,
    });

    return (
        <>
            <Head title={t('Interim settlement')} />

            <PageTitle
                title={t('Interim settlement')}
                description={t('Season :name', { name: season.name })}
            />

            <Panel>
                <PanelBody>
                    <form
                        className="grid max-w-xl gap-6"
                        onSubmit={(event) => {
                            event.preventDefault();
                            form.submit(
                                UpdateSeasonSettlementController(season.id),
                            );
                        }}
                    >
                        <SettlementSelect
                            matchdays={matchdays}
                            value={form.data.settlement_matchday}
                            onChange={(value) =>
                                form.setData('settlement_matchday', value)
                            }
                            error={form.errors.settlement_matchday}
                        />

                        <div className="flex items-center gap-4">
                            <Button
                                disabled={form.processing}
                                className="max-phone:h-11 max-phone:flex-1"
                            >
                                {t('Save')}
                            </Button>
                            <Button variant="ghost" asChild>
                                <Link href={show(season.id)}>
                                    {t('Cancel')}
                                </Link>
                            </Button>
                        </div>
                    </form>
                </PanelBody>
            </Panel>
        </>
    );
}

SeasonSettlement.layout = {
    breadcrumbs: [{ title: i18nKey('Seasons'), href: home() }],
};
