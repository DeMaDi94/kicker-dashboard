import { Head, router } from '@inertiajs/react';
import { PageTitle } from '@/components/core/page-title';
import { Panel, PanelHeader } from '@/components/core/panel';
import { StatTile } from '@/components/core/stat-tile';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { DailyChart } from '@/features/visits/daily-chart';
import { HourChart } from '@/features/visits/hour-chart';
import { PageTable } from '@/features/visits/page-table';
import type { VisitStatistics } from '@/features/visits/types';
import { VisitHeatmap } from '@/features/visits/visit-heatmap';
import { useTranslation } from '@/hooks/use-translation';
import { i18nKey } from '@/lib/i18n';
import { formatNumber } from '@/lib/number';
import { index as visitsRoute } from '@/routes/visits';

type VisitsProps = {
    days: number;
    periods: number[];
    statistics: VisitStatistics;
};

/*
 * VIS-04 – VIS-06 — when the public pages are visited, for admins: a chosen
 * period of the last days, today included, in German time.
 */
export default function Visits({ days, periods, statistics }: VisitsProps) {
    const { t, locale } = useTranslation();

    return (
        <>
            <Head title={t('Visitors')} />

            <PageTitle title={t('Visitors')}>
                <Select
                    value={String(days)}
                    onValueChange={(value) =>
                        router.visit(visitsRoute({ query: { days: value } }))
                    }
                >
                    <SelectTrigger
                        className="w-44 max-phone:w-full"
                        aria-label={t('Period')}
                    >
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        {periods.map((each) => (
                            <SelectItem key={each} value={String(each)}>
                                {t('Last :count days', { count: each })}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </PageTitle>

            <div className="grid gap-4">
                <div className="grid grid-cols-2 gap-3">
                    <StatTile
                        label={t('Visits')}
                        value={formatNumber(statistics.visits, locale)}
                    />
                    <StatTile
                        label={t('Visitors')}
                        value={formatNumber(statistics.visitors, locale)}
                        detail={t('Counted per day')}
                    />
                </div>

                <Panel>
                    <PanelHeader title={t('Visits and visitors per day')} />
                    <DailyChart days={statistics.days} />
                </Panel>

                <Panel>
                    <PanelHeader title={t('Visits by weekday and hour')} />
                    <VisitHeatmap heatmap={statistics.heatmap} />
                </Panel>

                <div className="grid gap-4 compact:grid-cols-2">
                    <Panel>
                        <PanelHeader title={t('Visits per hour')} />
                        <HourChart hours={statistics.hours} />
                    </Panel>

                    <Panel>
                        <PanelHeader title={t('Visits per page')} />
                        <PageTable pages={statistics.pages} />
                    </Panel>
                </div>
            </div>
        </>
    );
}

Visits.layout = {
    breadcrumbs: [{ title: i18nKey('Visitors'), href: visitsRoute() }],
};
