import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { PageTitle } from '@/components/core/page-title';
import { Panel, PanelHeader } from '@/components/core/panel';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { MatchdayTable } from '@/features/seasons/matchday-table';
import { PenaltyBox } from '@/features/seasons/penalty-box';
import { StandingsTable } from '@/features/seasons/standings-table';
import type {
    MatchdayBlock,
    PenaltyBox as PenaltyBoxData,
    SeasonOption,
    SeasonSummary,
    StandingLine,
} from '@/features/seasons/types';
import { useTranslation } from '@/hooks/use-translation';
import { i18nKey } from '@/lib/i18n';
import { formatCents } from '@/lib/money';
import { home } from '@/routes';
import { edit as editMatchday } from '@/routes/matchdays';
import { compare, create, show } from '@/routes/seasons';
import { edit as editPenaltyScale } from '@/routes/seasons/penalty-scale';
import { edit as editPlayers } from '@/routes/seasons/players';
import { edit as editSettlement } from '@/routes/seasons/settlement';

type ShowSeasonProps = {
    seasons: SeasonOption[];
    season: SeasonSummary | null;
    standings: StandingLine[];
    matchdays: MatchdayBlock[];
    penaltyBox: PenaltyBoxData | null;
};

/*
 * D6 — the matchday shown first: the one just saved (`?matchday=`), else
 * the last one with points, else the first.
 */
function initialMatchday(url: string, matchdays: MatchdayBlock[]): number {
    const asked = Number(
        new URLSearchParams(url.split('?')[1] ?? '').get('matchday'),
    );

    if (matchdays.some((each) => each.number === asked)) {
        return asked;
    }

    return matchdays.filter((each) => each.hasPoints).at(-1)?.number ?? 1;
}

/*
 * ACC-01 — the season view anyone can read: the overall table with points
 * and penalty sums, each matchday's result, and every season to switch to.
 */
export default function ShowSeason({
    seasons,
    season,
    standings,
    matchdays,
    penaltyBox,
}: ShowSeasonProps) {
    const { t, locale } = useTranslation();
    const { url, props } = usePage();
    const { auth } = props;
    const [matchdayNumber, setMatchdayNumber] = useState(() =>
        initialMatchday(url, matchdays),
    );
    const matchday = matchdays.find((each) => each.number === matchdayNumber);
    const can = (permission: string) => auth.permissions.includes(permission);

    return (
        <>
            <Head title={season?.name ?? t('Seasons')} />

            <PageTitle
                title={
                    season === null
                        ? t('Seasons')
                        : t('Season :name', { name: season.name })
                }
                description={
                    season === null
                        ? undefined
                        : t(
                              'Penalty per matchday: :start for the lowest score, :step less for each higher one.',
                              {
                                  start: formatCents(
                                      season.penaltyStartCents,
                                      locale,
                                  ),
                                  step: formatCents(
                                      season.penaltyStepCents,
                                      locale,
                                  ),
                              },
                          ) +
                          (season.settlementMatchday === null
                              ? ''
                              : ` ${t('Interim settlement after matchday :number.', { number: season.settlementMatchday })}`)
                }
            >
                <div className="flex flex-wrap items-center gap-2">
                    {seasons.length > 1 && season !== null && (
                        <Select
                            value={String(season.id)}
                            onValueChange={(id) =>
                                router.visit(show(Number(id)))
                            }
                        >
                            <SelectTrigger
                                className="w-40"
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
                    {season !== null && (
                        <Button variant="outline" asChild>
                            <Link href={compare(season.id)}>
                                {t('Head-to-head')}
                            </Link>
                        </Button>
                    )}
                    {season !== null && can('seasons.set-settlement') && (
                        <Button variant="outline" asChild>
                            <Link href={editSettlement(season.id)}>
                                {t('Interim settlement')}
                            </Link>
                        </Button>
                    )}
                    {season !== null && auth.user !== null && (
                        <Button variant="outline" asChild>
                            <Link href={editPenaltyScale(season.id)}>
                                {t('Penalty scale')}
                            </Link>
                        </Button>
                    )}
                    {season !== null && can('seasons.set-players') && (
                        <Button variant="outline" asChild>
                            <Link href={editPlayers(season.id)}>
                                {t('Players of the season')}
                            </Link>
                        </Button>
                    )}
                    {can('seasons.create') && (
                        <Button asChild>
                            <Link href={create()}>{t('Create season')}</Link>
                        </Button>
                    )}
                </div>
            </PageTitle>

            {season === null ? (
                <Panel>
                    <p className="px-4 py-8 text-center text-sm text-brand-muted">
                        {t('There is no season yet.')}
                    </p>
                </Panel>
            ) : (
                <div className="flex flex-col gap-4">
                    <Panel>
                        <PanelHeader title={t('Overall table')} />
                        <StandingsTable
                            seasonId={season.id}
                            rows={standings}
                            split={season.settlementMatchday !== null}
                        />
                    </Panel>

                    <Panel>
                        <PanelHeader title={t('Matchday')}>
                            <Select
                                value={String(matchdayNumber)}
                                onValueChange={(value) =>
                                    setMatchdayNumber(Number(value))
                                }
                            >
                                <SelectTrigger
                                    className="w-36 max-phone:flex-1"
                                    aria-label={t('Matchday')}
                                >
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {matchdays.map((each) => (
                                        <SelectItem
                                            key={each.number}
                                            value={String(each.number)}
                                        >
                                            {t('Matchday :number', {
                                                number: each.number,
                                            })}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {auth.user !== null && (
                                <Button size="sm" asChild>
                                    <Link
                                        href={editMatchday({
                                            season: season.id,
                                            matchday: matchdayNumber,
                                        })}
                                    >
                                        {t('Enter points')}
                                    </Link>
                                </Button>
                            )}
                        </PanelHeader>
                        {matchday && (
                            <MatchdayTable
                                seasonId={season.id}
                                matchday={matchday}
                            />
                        )}
                    </Panel>

                    {penaltyBox && (
                        <Panel>
                            <PanelHeader title={t('Penalty box')} />
                            <PenaltyBox
                                seasonId={season.id}
                                box={penaltyBox}
                                settlementMatchday={season.settlementMatchday}
                            />
                        </Panel>
                    )}
                </div>
            )}
        </>
    );
}

ShowSeason.layout = {
    breadcrumbs: [{ title: i18nKey('Seasons'), href: home() }],
};
