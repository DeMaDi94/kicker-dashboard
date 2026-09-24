import { Head, Link, router } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { PageTitle } from '@/components/core/page-title';
import { Panel, PanelBody, PanelHeader } from '@/components/core/panel';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type {
    LeagueRecord,
    LeagueRecords,
    Option,
    RecordHolder,
} from '@/features/statistics/types';
import { useTranslation } from '@/hooks/use-translation';
import { i18nKey } from '@/lib/i18n';
import { formatCents } from '@/lib/money';
import { records as recordsRoute } from '@/routes';
import { show } from '@/routes/players';

type RecordsProps = {
    seasons: Option[];
    season: number | null;
    records: LeagueRecords;
};

/* A select cannot hold an empty value; this stands for "all seasons". */
const ALL = 'all';

/*
 * STAT-10 — the league's records, over all seasons or one: who holds each,
 * in which season and on which matchday.
 */
export default function Records({ seasons, season, records }: RecordsProps) {
    const { t, locale } = useTranslation();

    const holder = (each: RecordHolder) => {
        const parts: ReactNode[] = [];

        if (each.playerId !== null && each.playerName !== null) {
            parts.push(
                <Link
                    key="player"
                    href={show(each.playerId, {
                        query: { season: each.seasonId },
                    })}
                    className="font-medium text-brand-ink underline-offset-2 hover:underline"
                >
                    {each.playerName}
                </Link>,
            );
        }

        parts.push(<span key="season">{each.seasonName}</span>);

        if (each.matchday !== null) {
            parts.push(
                <span key="matchday">
                    {t('Matchday :number', { number: each.matchday })}
                </span>,
            );
        }

        return parts.flatMap((part, index) =>
            index === 0
                ? [part]
                : [<span key={`dot-${index}`}> · </span>, part],
        );
    };

    const card = (
        title: string,
        record: LeagueRecord | null,
        value: (value: number) => string,
        note?: string,
    ) => (
        <Panel>
            <PanelHeader title={title} />
            <PanelBody className="grid gap-2">
                {note && <p className="text-xs text-brand-muted">{note}</p>}
                {record === null ? (
                    <p className="text-sm text-brand-muted">–</p>
                ) : (
                    <>
                        <p className="brand-figure text-2xl font-semibold text-brand-ink">
                            {value(record.value)}
                        </p>
                        <ul className="grid gap-1 text-sm text-brand-muted">
                            {record.holders.map((each, index) => (
                                <li key={index}>{holder(each)}</li>
                            ))}
                        </ul>
                    </>
                )}
            </PanelBody>
        </Panel>
    );

    const points = (value: number) => t(':count points', { count: value });

    return (
        <>
            <Head title={t('Records')} />

            <PageTitle
                title={t('Records')}
                description={
                    season === null
                        ? t('Over all seasons')
                        : t('Season :name', {
                              name:
                                  seasons.find((each) => each.id === season)
                                      ?.name ?? '',
                          })
                }
            >
                {seasons.length > 0 && (
                    <Select
                        value={season === null ? ALL : String(season)}
                        onValueChange={(value) =>
                            router.visit(
                                recordsRoute(
                                    value === ALL
                                        ? undefined
                                        : { query: { season: value } },
                                ),
                            )
                        }
                    >
                        <SelectTrigger
                            className="w-44 max-phone:w-full"
                            aria-label={t('Season')}
                        >
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={ALL}>
                                {t('All seasons')}
                            </SelectItem>
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
            </PageTitle>

            <div className="grid gap-4 compact:grid-cols-2">
                {card(
                    t('Highest score on a matchday'),
                    records.highestScore,
                    points,
                )}
                {card(
                    t('Lowest score on a matchday'),
                    records.lowestScore,
                    points,
                )}
                {card(
                    t('Most matchday wins in a season'),
                    records.mostWins,
                    (value) => t(':count wins', { count: value }),
                )}
                {card(
                    t('Highest penalties in a season'),
                    records.highestPenalty,
                    (value) => formatCents(value, locale),
                )}
                {card(
                    t('Closest matchday'),
                    records.closestMatchday,
                    points,
                    t('The gap between the highest and the lowest score.'),
                )}
            </div>
        </>
    );
}

Records.layout = {
    breadcrumbs: [{ title: i18nKey('Records'), href: recordsRoute() }],
};
