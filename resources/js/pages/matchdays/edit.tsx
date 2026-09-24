import { Form, Head, Link, router, setLayoutProps } from '@inertiajs/react';
import UpdateMatchdayController from '@/actions/App/Http/Matchdays/UpdateMatchday/UpdateMatchdayController';
import { PageTitle } from '@/components/core/page-title';
import { Panel, PanelBody } from '@/components/core/panel';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { PlayerName } from '@/features/seasons/player-name';
import { seasonTrail } from '@/features/seasons/season-trail';
import { useTranslation } from '@/hooks/use-translation';
import { i18nKey } from '@/lib/i18n';
import { home } from '@/routes';
import { edit } from '@/routes/matchdays';
import { show } from '@/routes/seasons';

type EditMatchdayProps = {
    season: { id: number; name: string };
    matchday: number;
    matchdays: number[];
    players: {
        id: number;
        name: string;
        alias: string;
        points: number | null;
    }[];
};

/*
 * MD-01 — the points of one matchday, from the kicker Manager, for every
 * player of the season at once: whole numbers, negative allowed. MD-04 —
 * entered points can be changed here too.
 */
export default function EditMatchday({
    season,
    matchday,
    matchdays,
    players,
}: EditMatchdayProps) {
    const { t } = useTranslation();

    setLayoutProps({
        breadcrumbs: [
            ...seasonTrail(season),
            {
                title: t('Matchday :number', { number: matchday }),
                href: edit([season.id, matchday]),
                verbatim: true,
            },
        ],
    });

    return (
        <>
            <Head title={t('Enter points')} />

            <PageTitle
                title={t('Enter points')}
                description={t('Season :name', { name: season.name })}
            >
                <Select
                    value={String(matchday)}
                    onValueChange={(value) =>
                        router.visit(
                            edit({
                                season: season.id,
                                matchday: Number(value),
                            }),
                        )
                    }
                >
                    <SelectTrigger className="w-36" aria-label={t('Matchday')}>
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        {matchdays.map((each) => (
                            <SelectItem key={each} value={String(each)}>
                                {t('Matchday :number', { number: each })}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </PageTitle>

            <Panel>
                <PanelBody>
                    {players.length === 0 ? (
                        <p className="text-sm text-brand-muted">
                            {t('This season has no players yet.')}
                        </p>
                    ) : (
                        <Form
                            key={matchday}
                            {...UpdateMatchdayController.form({
                                season: season.id,
                                matchday,
                            })}
                            className="grid max-w-md gap-4"
                        >
                            {({ processing, errors }) => (
                                <>
                                    {players.map((player) => (
                                        <div
                                            key={player.id}
                                            className="grid grid-cols-[1fr_7rem] items-center gap-x-4 gap-y-1"
                                        >
                                            <Label
                                                htmlFor={`points-${player.id}`}
                                            >
                                                <PlayerName
                                                    name={player.name}
                                                    alias={player.alias}
                                                />
                                            </Label>
                                            <Input
                                                id={`points-${player.id}`}
                                                name={`points[${player.id}]`}
                                                type="number"
                                                step={1}
                                                required
                                                /* MD-01 allows negative points, and a
                                                   numeric input mode has no minus key
                                                   on every phone; `number` alone keeps
                                                   one. */
                                                enterKeyHint="next"
                                                className="text-right max-phone:h-11"
                                                defaultValue={
                                                    player.points ?? ''
                                                }
                                            />
                                            <InputError
                                                className="col-span-2"
                                                message={
                                                    errors[
                                                        `points.${player.id}`
                                                    ]
                                                }
                                            />
                                        </div>
                                    ))}

                                    <InputError message={errors.points} />

                                    <div className="flex items-center gap-4">
                                        <Button
                                            disabled={processing}
                                            className="max-phone:h-11 max-phone:flex-1"
                                        >
                                            {t('Save points')}
                                        </Button>
                                        <Button variant="ghost" asChild>
                                            <Link href={show(season.id)}>
                                                {t('Cancel')}
                                            </Link>
                                        </Button>
                                    </div>
                                </>
                            )}
                        </Form>
                    )}
                </PanelBody>
            </Panel>
        </>
    );
}

EditMatchday.layout = {
    breadcrumbs: [{ title: i18nKey('Seasons'), href: home() }],
};
