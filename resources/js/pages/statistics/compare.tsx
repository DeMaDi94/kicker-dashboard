import { Head, router } from '@inertiajs/react';
import { PageTitle } from '@/components/core/page-title';
import { Panel, PanelBody, PanelHeader } from '@/components/core/panel';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { DuelChart } from '@/features/statistics/duel-chart';
import { StatTile } from '@/features/statistics/stat-tile';
import type { HeadToHead, Option } from '@/features/statistics/types';
import { useTranslation } from '@/hooks/use-translation';
import { i18nKey } from '@/lib/i18n';
import { home } from '@/routes';
import { compare } from '@/routes/seasons';

type ComparePlayersProps = {
    season: Option;
    players: Option[];
    a: number | null;
    b: number | null;
    duel: HeadToHead | null;
};

/*
 * STAT-09 — two players of a season side by side: both point curves and how
 * often each had more points than the other.
 */
export default function ComparePlayers({
    season,
    players,
    a,
    b,
    duel,
}: ComparePlayersProps) {
    const { t } = useTranslation();
    const nameOf = (id: number | null) =>
        players.find((player) => player.id === id)?.name ?? '';

    const choose = (side: 'a' | 'b', id: string) =>
        router.visit(
            compare(season.id, {
                query: {
                    a: side === 'a' ? id : (a ?? ''),
                    b: side === 'b' ? id : (b ?? ''),
                },
            }),
            { preserveScroll: true },
        );

    const picker = (side: 'a' | 'b', value: number | null, label: string) => (
        <div className="grid min-w-0 flex-1 gap-2">
            <Label htmlFor={`player-${side}`}>{label}</Label>
            <Select
                value={value === null ? undefined : String(value)}
                onValueChange={(id) => choose(side, id)}
            >
                <SelectTrigger id={`player-${side}`} className="w-full">
                    <SelectValue placeholder={t('Choose a player')} />
                </SelectTrigger>
                <SelectContent>
                    {players.map((player) => (
                        <SelectItem key={player.id} value={String(player.id)}>
                            {player.name}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
        </div>
    );

    return (
        <>
            <Head title={t('Head-to-head')} />

            <PageTitle
                title={t('Head-to-head')}
                description={t('Season :name', { name: season.name })}
            />

            <div className="flex flex-col gap-4">
                <Panel>
                    <PanelBody className="flex flex-wrap gap-4">
                        {picker('a', a, t('Player'))}
                        {picker('b', b, t('Opponent'))}
                    </PanelBody>
                </Panel>

                {duel === null ? (
                    <Panel>
                        <p className="px-4 py-8 text-center text-sm text-brand-muted">
                            {t('Choose two players to compare them.')}
                        </p>
                    </Panel>
                ) : (
                    <>
                        <Panel>
                            <PanelHeader title={t('Who had more points?')} />
                            <PanelBody className="grid grid-cols-3 gap-2">
                                <StatTile
                                    label={nameOf(a)}
                                    value={duel.aAhead}
                                    detail={t('matchdays ahead')}
                                />
                                <StatTile
                                    label={t('Level')}
                                    value={duel.level}
                                    detail={t('matchdays')}
                                />
                                <StatTile
                                    label={nameOf(b)}
                                    value={duel.bAhead}
                                    detail={t('matchdays ahead')}
                                />
                            </PanelBody>
                        </Panel>

                        {duel.lines.length > 0 && (
                            <Panel>
                                <PanelHeader title={t('Points per matchday')} />
                                <DuelChart
                                    duel={duel}
                                    aName={nameOf(a)}
                                    bName={nameOf(b)}
                                />
                            </Panel>
                        )}
                    </>
                )}
            </div>
        </>
    );
}

ComparePlayers.layout = {
    breadcrumbs: [{ title: i18nKey('Seasons'), href: home() }],
};
