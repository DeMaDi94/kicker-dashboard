import { Head, Link, useForm } from '@inertiajs/react';
import StoreSeasonController from '@/actions/App/Http/Seasons/StoreSeason/StoreSeasonController';
import { NumberInput } from '@/components/core/number-input';
import { PageTitle } from '@/components/core/page-title';
import { Panel, PanelBody } from '@/components/core/panel';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { PlayerChecklist } from '@/features/seasons/player-checklist';
import type { PlayerOption } from '@/features/seasons/types';
import { useTranslation } from '@/hooks/use-translation';
import { i18nKey } from '@/lib/i18n';
import { home } from '@/routes';
import { create } from '@/routes/seasons';

type CreateSeasonProps = {
    players: PlayerOption[];
};

/*
 * SEA-01 — a season's name and penalty scale; SEA-02 — its players, which
 * can still change until the first points are entered (SEA-03).
 */
export default function CreateSeason({ players }: CreateSeasonProps) {
    const { t } = useTranslation();
    const form = useForm<{
        name: string;
        penalty_start: number | null;
        penalty_step: number | null;
        player_ids: number[];
    }>({ name: '', penalty_start: null, penalty_step: null, player_ids: [] });

    return (
        <>
            <Head title={t('Create season')} />

            <PageTitle title={t('Create season')} />

            <Panel>
                <PanelBody>
                    <form
                        className="grid max-w-xl gap-6"
                        onSubmit={(event) => {
                            event.preventDefault();
                            form.submit(StoreSeasonController());
                        }}
                    >
                        <div className="grid gap-2">
                            <Label htmlFor="name">{t('Name')}</Label>
                            <Input
                                id="name"
                                required
                                autoComplete="off"
                                placeholder={t('e.g. 2025/26')}
                                value={form.data.name}
                                onChange={(event) =>
                                    form.setData('name', event.target.value)
                                }
                            />
                            <InputError message={form.errors.name} />
                        </div>

                        <div className="grid gap-4 sm:grid-cols-2">
                            <div className="grid gap-2">
                                <Label htmlFor="penalty_start">
                                    {t('Start amount (€)')}
                                </Label>
                                <NumberInput
                                    id="penalty_start"
                                    required
                                    decimals={2}
                                    value={form.data.penalty_start}
                                    onValueChange={(value) =>
                                        form.setData('penalty_start', value)
                                    }
                                />
                                <InputError
                                    message={form.errors.penalty_start}
                                />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="penalty_step">
                                    {t('Step (€)')}
                                </Label>
                                <NumberInput
                                    id="penalty_step"
                                    required
                                    decimals={2}
                                    value={form.data.penalty_step}
                                    onValueChange={(value) =>
                                        form.setData('penalty_step', value)
                                    }
                                />
                                <InputError
                                    message={form.errors.penalty_step}
                                />
                            </div>
                        </div>

                        <PlayerChecklist
                            players={players}
                            selected={form.data.player_ids}
                            onChange={(ids) => form.setData('player_ids', ids)}
                            error={form.errors.player_ids}
                        />

                        <div className="flex items-center gap-4">
                            <Button disabled={form.processing}>
                                {t('Create season')}
                            </Button>
                            <Button variant="ghost" asChild>
                                <Link href={home()}>{t('Cancel')}</Link>
                            </Button>
                        </div>
                    </form>
                </PanelBody>
            </Panel>
        </>
    );
}

CreateSeason.layout = {
    breadcrumbs: [
        { title: i18nKey('Seasons'), href: home() },
        { title: i18nKey('Create season'), href: create() },
    ],
};
