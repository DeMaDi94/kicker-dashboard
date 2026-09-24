import { Head, Link, router, setLayoutProps } from '@inertiajs/react';
import { PageTitle } from '@/components/core/page-title';
import { Panel, PanelBody, PanelHeader } from '@/components/core/panel';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { FormStrip } from '@/features/statistics/form-strip';
import { PenaltyChart } from '@/features/statistics/penalty-chart';
import { PlaceChart } from '@/features/statistics/place-chart';
import { PointsChart } from '@/features/statistics/points-chart';
import { StatTile } from '@/features/statistics/stat-tile';
import type {
    CareerStats,
    Option,
    PlayerSeasonStats,
} from '@/features/statistics/types';
import { useTranslation } from '@/hooks/use-translation';
import { i18nKey } from '@/lib/i18n';
import { formatCents } from '@/lib/money';
import { formatNumber } from '@/lib/number';
import { index, show } from '@/routes/players';
import { compare } from '@/routes/seasons';

type PlayerStatisticsProps = {
    player: { id: number; name: string; alias: string };
    seasons: Option[];
    season: (Option & { settlementMatchday: number | null }) | null;
    stats: PlayerSeasonStats | null;
    career: CareerStats;
    opponents: Option[];
};

/*
 * STAT-01 — a player's own page, readable by anyone: one season's figures
 * and graphs (STAT-03–07), and the all-time balance (STAT-08).
 */
export default function PlayerStatistics({
    player,
    seasons,
    season,
    stats,
    career,
    opponents,
}: PlayerStatisticsProps) {
    const { t, locale } = useTranslation();

    setLayoutProps({
        breadcrumbs: [
            { title: i18nKey('Players'), href: index() },
            { title: player.name, href: show(player.id), verbatim: true },
        ],
    });
    const euros = (cents: number) => formatCents(cents, locale);
    const decimal = (value: number | null) =>
        value === null ? '–' : formatNumber(value, locale, 1);
    const matchdays = (numbers: number[]) =>
        numbers.map((number) => t('Matchday :number', { number })).join(', ');

    return (
        <>
            <Head title={player.name} />

            <PageTitle title={player.name} description={player.alias}>
                <div className="flex flex-wrap items-center gap-2">
                    {seasons.length > 1 && season !== null && (
                        <Select
                            value={String(season.id)}
                            onValueChange={(id) =>
                                router.visit(
                                    show(player.id, {
                                        query: { season: id },
                                    }),
                                )
                            }
                        >
                            <SelectTrigger
                                className="w-40 max-phone:flex-1"
                                aria-label={t('Season')}
                            >
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {seasons.map((each) => (
                                    <SelectItem
                                        key={each.id}
                                        value={String(each.id)}
                                    >
                                        {each.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    )}
                    {season !== null && opponents.length > 0 && (
                        <Button variant="outline" asChild>
                            <Link
                                href={compare(season.id, {
                                    query: { a: player.id },
                                })}
                            >
                                {t('Head-to-head')}
                            </Link>
                        </Button>
                    )}
                </div>
            </PageTitle>

            <div className="flex flex-col gap-4">
                {season === null || stats === null ? (
                    <Panel>
                        <p className="px-4 py-8 text-center text-sm text-brand-muted">
                            {t('This player has not played a season yet.')}
                        </p>
                    </Panel>
                ) : (
                    <>
                        <Panel>
                            <PanelHeader
                                title={t('Season :name', { name: season.name })}
                                badge={t(':count matchdays', {
                                    count: stats.matchdaysPlayed,
                                })}
                            />
                            <PanelBody className="grid grid-cols-2 gap-2 compact:grid-cols-3">
                                <StatTile
                                    label={t('Place')}
                                    value={
                                        stats.matchdaysPlayed > 0
                                            ? `${stats.place}.`
                                            : '–'
                                    }
                                />
                                <StatTile
                                    label={t('Points')}
                                    value={stats.totalPoints}
                                />
                                <StatTile
                                    label={t('Average points')}
                                    value={decimal(stats.averagePoints)}
                                />
                                <StatTile
                                    label={t('Best matchday')}
                                    value={stats.bestPoints ?? '–'}
                                    detail={matchdays(stats.bestMatchdays)}
                                />
                                <StatTile
                                    label={t('Worst matchday')}
                                    value={stats.worstPoints ?? '–'}
                                    detail={matchdays(stats.worstMatchdays)}
                                />
                                <StatTile
                                    label={t('Penalties')}
                                    value={euros(stats.penaltyCents)}
                                    detail={
                                        stats.firstHalfPenaltyCents === null ||
                                        stats.secondHalfPenaltyCents ===
                                            null ? undefined : (
                                            <>
                                                {t('First half')}{' '}
                                                {euros(
                                                    stats.firstHalfPenaltyCents,
                                                )}{' '}
                                                · {t('Second half')}{' '}
                                                {euros(
                                                    stats.secondHalfPenaltyCents,
                                                )}
                                            </>
                                        )
                                    }
                                />
                            </PanelBody>
                        </Panel>

                        {stats.lines.length > 0 && (
                            <>
                                <Panel>
                                    <PanelHeader
                                        title={t('Points per matchday')}
                                    />
                                    <PointsChart lines={stats.lines} />
                                </Panel>

                                <Panel>
                                    <PanelHeader
                                        title={t('Place in the overall table')}
                                    />
                                    <PlaceChart
                                        lines={stats.lines}
                                        players={opponents.length + 1}
                                    />
                                </Panel>

                                <Panel>
                                    <PanelHeader title={t('Penalties')} />
                                    <PenaltyChart
                                        lines={stats.lines}
                                        settlementMatchday={
                                            season.settlementMatchday
                                        }
                                    />
                                </Panel>
                            </>
                        )}

                        <Panel>
                            <PanelHeader
                                title={t('Achievements and streaks')}
                            />
                            <PanelBody className="grid gap-4">
                                <div className="grid grid-cols-2 gap-2 compact:grid-cols-4">
                                    <StatTile
                                        label={t('Matchday wins')}
                                        value={stats.wins}
                                    />
                                    <StatTile
                                        label={t('Rote Laterne')}
                                        value={stats.lanterns}
                                    />
                                    <StatTile
                                        label={t('Penalty-free matchdays')}
                                        value={stats.penaltyFreeMatchdays}
                                    />
                                    <StatTile
                                        label={t('Longest penalty-free run')}
                                        value={stats.longestPenaltyFreeStreak}
                                    />
                                </div>
                                <div className="grid gap-2">
                                    <span className="brand-label text-brand-label">
                                        {t('Form: last five matchdays')}
                                    </span>
                                    <FormStrip form={stats.form} />
                                </div>
                            </PanelBody>
                        </Panel>

                        {stats.lines.length > 0 && (
                            <Panel>
                                <details>
                                    <summary className="cursor-pointer px-4 py-3 text-sm font-semibold text-brand-ink">
                                        {t('All matchdays as a table')}
                                    </summary>
                                    <div className="relative overflow-x-auto">
                                        <table className="w-full text-sm">
                                            <thead className="border-y border-brand-line-soft text-brand-muted">
                                                <tr>
                                                    <th
                                                        scope="col"
                                                        className="px-2 py-2 text-right font-medium phone:px-3"
                                                    >
                                                        {t('Matchday')}
                                                    </th>
                                                    <th
                                                        scope="col"
                                                        className="px-2 py-2 text-right font-medium phone:px-3"
                                                    >
                                                        {t('Points')}
                                                    </th>
                                                    <th
                                                        scope="col"
                                                        className="px-2 py-2 text-right font-medium phone:px-3"
                                                    >
                                                        {t('League average')}
                                                    </th>
                                                    <th
                                                        scope="col"
                                                        className="px-2 py-2 text-right font-medium phone:px-3"
                                                    >
                                                        {t('Place')}
                                                    </th>
                                                    <th
                                                        scope="col"
                                                        className="px-2 py-2 text-right font-medium phone:px-3"
                                                    >
                                                        {t('Penalty')}
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {stats.lines.map((line) => (
                                                    <tr
                                                        key={line.matchday}
                                                        className="border-b border-brand-line-soft last:border-0"
                                                    >
                                                        <td className="px-2 py-2 text-right brand-figure phone:px-3">
                                                            {line.matchday}.
                                                        </td>
                                                        <td className="px-2 py-2 text-right brand-figure phone:px-3">
                                                            {line.points}
                                                        </td>
                                                        <td className="px-2 py-2 text-right brand-figure phone:px-3">
                                                            {decimal(
                                                                line.leagueAverage,
                                                            )}
                                                        </td>
                                                        <td className="px-2 py-2 text-right brand-figure phone:px-3">
                                                            {line.dayPlace}.
                                                        </td>
                                                        <td className="px-2 py-2 text-right brand-figure whitespace-nowrap phone:px-3">
                                                            {euros(
                                                                line.penaltyCents,
                                                            )}
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                </details>
                            </Panel>
                        )}
                    </>
                )}

                <Panel>
                    <PanelHeader title={t('All-time balance')} />
                    <PanelBody className="grid grid-cols-2 gap-2 compact:grid-cols-5">
                        <StatTile
                            label={t('Seasons played')}
                            value={career.seasonsPlayed}
                        />
                        <StatTile
                            label={t('Average place')}
                            value={decimal(career.averagePlace)}
                        />
                        <StatTile
                            label={t('Points')}
                            value={career.totalPoints}
                        />
                        <StatTile
                            label={t('Penalties')}
                            value={euros(career.totalPenaltyCents)}
                        />
                        <StatTile
                            label={t('Matchday wins')}
                            value={career.totalWins}
                        />
                    </PanelBody>
                </Panel>
            </div>
        </>
    );
}

PlayerStatistics.layout = {
    breadcrumbs: [{ title: i18nKey('Players'), href: index() }],
};
