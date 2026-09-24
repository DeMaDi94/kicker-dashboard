import { Head, Link, useForm } from '@inertiajs/react';
import UpdatePenaltyScaleController from '@/actions/App/Http/Seasons/UpdatePenaltyScale/UpdatePenaltyScaleController';
import { NumberInput } from '@/components/core/number-input';
import { PageTitle } from '@/components/core/page-title';
import { Panel, PanelBody } from '@/components/core/panel';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { useTranslation } from '@/hooks/use-translation';
import { i18nKey } from '@/lib/i18n';
import { home } from '@/routes';
import { show } from '@/routes/seasons';

type PenaltyScaleProps = {
    season: {
        id: number;
        name: string;
        penaltyStartCents: number;
        penaltyStepCents: number;
    };
};

/*
 * SEA-05 — any signed-in user changes a season's start amount and step at
 * any time; every matchday's penalty follows the new values.
 */
export default function PenaltyScale({ season }: PenaltyScaleProps) {
    const { t } = useTranslation();
    const form = useForm<{
        penalty_start: number | null;
        penalty_step: number | null;
    }>({
        penalty_start: season.penaltyStartCents / 100,
        penalty_step: season.penaltyStepCents / 100,
    });

    return (
        <>
            <Head title={t('Penalty scale')} />

            <PageTitle
                title={t('Penalty scale')}
                description={t('Season :name', { name: season.name })}
            />

            <Panel>
                <PanelBody>
                    <form
                        className="grid max-w-xl gap-6"
                        onSubmit={(event) => {
                            event.preventDefault();
                            form.submit(
                                UpdatePenaltyScaleController(season.id),
                            );
                        }}
                    >
                        {/* SEA-05 — the new values apply to every matchday. */}
                        <p className="text-[13px] text-brand-muted">
                            {t(
                                'The penalties of every matchday of this season are recalculated with the new values.',
                            )}
                        </p>
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

PenaltyScale.layout = {
    breadcrumbs: [{ title: i18nKey('Seasons'), href: home() }],
};
