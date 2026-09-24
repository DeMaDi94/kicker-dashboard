import { Head, Link, setLayoutProps, useForm } from '@inertiajs/react';
import UpdateSeasonPlayersController from '@/actions/App/Http/Seasons/UpdateSeasonPlayers/UpdateSeasonPlayersController';
import { PageTitle } from '@/components/core/page-title';
import { Panel, PanelBody } from '@/components/core/panel';
import { Button } from '@/components/ui/button';
import { PlayerChecklist } from '@/features/seasons/player-checklist';
import type { PlayerOption, SeasonOption } from '@/features/seasons/types';
import { seasonTrail } from '@/features/seasons/season-trail';
import { useTranslation } from '@/hooks/use-translation';
import { i18nKey } from '@/lib/i18n';
import { home } from '@/routes';
import { show } from '@/routes/seasons';
import seasonPlayers from '@/routes/seasons/players';

type SeasonPlayersProps = {
    season: SeasonOption;
    players: PlayerOption[];
    selected: number[];
    locked: boolean;
};

/*
 * SEA-02 — the players of a season; SEA-03 — fixed once points are entered.
 */
export default function SeasonPlayers({
    season,
    players,
    selected,
    locked,
}: SeasonPlayersProps) {
    const { t } = useTranslation();

    setLayoutProps({
        breadcrumbs: [
            ...seasonTrail(season),
            {
                title: i18nKey('Players of the season'),
                href: seasonPlayers.edit(season.id),
            },
        ],
    });
    const form = useForm<{ player_ids: number[] }>({ player_ids: selected });

    return (
        <>
            <Head title={t('Players of the season')} />

            <PageTitle
                title={t('Players of the season')}
                description={
                    locked
                        ? t(
                              'Points have been entered for this season, so its players can no longer change.',
                          )
                        : undefined
                }
            />

            <Panel>
                <PanelBody>
                    <form
                        className="grid max-w-xl gap-6"
                        onSubmit={(event) => {
                            event.preventDefault();
                            form.submit(
                                UpdateSeasonPlayersController(season.id),
                            );
                        }}
                    >
                        <PlayerChecklist
                            players={players}
                            selected={form.data.player_ids}
                            onChange={(ids) => form.setData('player_ids', ids)}
                            disabled={locked}
                            error={form.errors.player_ids}
                        />

                        <div className="flex items-center gap-4">
                            {!locked && (
                                <Button disabled={form.processing}>
                                    {t('Save')}
                                </Button>
                            )}
                            <Button variant="ghost" asChild>
                                <Link href={show(season.id)}>{t('Back')}</Link>
                            </Button>
                        </div>
                    </form>
                </PanelBody>
            </Panel>
        </>
    );
}

SeasonPlayers.layout = {
    breadcrumbs: [{ title: i18nKey('Seasons'), href: home() }],
};
