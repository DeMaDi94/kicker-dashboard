import { Head, Link, router, setLayoutProps, usePage } from '@inertiajs/react';
import { ChevronDown } from 'lucide-react';
import { useState } from 'react';
import { useConfirm } from '@/components/core/dialogs';
import { PageTitle } from '@/components/core/page-title';
import { Panel, PanelHeader } from '@/components/core/panel';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
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
import { seasonTrail } from '@/features/seasons/season-trail';
import { useTranslation } from '@/hooks/use-translation';
import { i18nKey } from '@/lib/i18n';
import { formatCents } from '@/lib/money';
import { home } from '@/routes';
import { edit as editMatchday } from '@/routes/matchdays';
import { compare, create, deleted, destroy, show } from '@/routes/seasons';
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

    setLayoutProps({
        breadcrumbs:
            season === null
                ? [{ title: i18nKey('Seasons'), href: home() }]
                : seasonTrail(season),
    });
    const [matchdayNumber, setMatchdayNumber] = useState(() =>
        initialMatchday(url, matchdays),
    );
    const matchday = matchdays.find((each) => each.number === matchdayNumber);
    const can = (permission: string) => auth.permissions.includes(permission);
    const confirm = useConfirm();

    // SEA-06 — deleting a season asks first, naming the season.
    const deleteSeason = async (target: SeasonSummary) => {
        const confirmed = await confirm(
            t(
                'Season :name disappears from all views and statistics. Its points are kept, and an admin can restore it.',
                { name: target.name },
            ),
            {
                title: t('Delete season?'),
                okLabel: t('Delete season'),
                danger: true,
            },
        );

        if (confirmed) {
            router.delete(destroy(target.id));
        }
    };

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
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button variant="outline">
                                {t('Actions')}
                                <ChevronDown />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" className="min-w-56">
                            {season !== null && (
                                <DropdownMenuItem asChild>
                                    <Link href={compare(season.id)}>
                                        {t('Head-to-head')}
                                    </Link>
                                </DropdownMenuItem>
                            )}
                            {season !== null &&
                                can('seasons.set-settlement') && (
                                    <DropdownMenuItem asChild>
                                        <Link href={editSettlement(season.id)}>
                                            {t('Interim settlement')}
                                        </Link>
                                    </DropdownMenuItem>
                                )}
                            {season !== null && auth.user !== null && (
                                <DropdownMenuItem asChild>
                                    <Link href={editPenaltyScale(season.id)}>
                                        {t('Penalty scale')}
                                    </Link>
                                </DropdownMenuItem>
                            )}
                            {season !== null && can('seasons.set-players') && (
                                <DropdownMenuItem asChild>
                                    <Link href={editPlayers(season.id)}>
                                        {t('Players of the season')}
                                    </Link>
                                </DropdownMenuItem>
                            )}
                            {can('seasons.create') && (
                                <DropdownMenuItem asChild>
                                    <Link href={create()}>
                                        {t('Create season')}
                                    </Link>
                                </DropdownMenuItem>
                            )}
                            {can('seasons.delete') && (
                                <>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem asChild>
                                        <Link href={deleted()}>
                                            {t('Deleted seasons')}
                                        </Link>
                                    </DropdownMenuItem>
                                    {season !== null && (
                                        <DropdownMenuItem
                                            onSelect={() =>
                                                deleteSeason(season)
                                            }
                                        >
                                            {t('Delete season')}
                                        </DropdownMenuItem>
                                    )}
                                </>
                            )}
                        </DropdownMenuContent>
                    </DropdownMenu>
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
