import { Form, Head, Link, setLayoutProps } from '@inertiajs/react';
import UpdatePlayerController from '@/actions/App/Http/Players/UpdatePlayer/UpdatePlayerController';
import { PageTitle } from '@/components/core/page-title';
import { Panel, PanelBody } from '@/components/core/panel';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useTranslation } from '@/hooks/use-translation';
import { i18nKey } from '@/lib/i18n';
import { edit, index } from '@/routes/players';

type EditPlayerProps = {
    player: { id: number; name: string; alias: string };
};

/*
 * PLY-02 — an admin changes a player's name and alias; the change holds in
 * every season.
 */
export default function EditPlayer({ player }: EditPlayerProps) {
    const { t } = useTranslation();

    setLayoutProps({
        breadcrumbs: [
            { title: i18nKey('Players'), href: index() },
            { title: player.name, href: edit(player.id), verbatim: true },
        ],
    });

    return (
        <>
            <Head title={t('Edit player')} />

            <PageTitle title={t('Edit player')} />

            <Panel>
                <PanelBody>
                    <Form
                        {...UpdatePlayerController.form(player.id)}
                        className="grid max-w-xl gap-6"
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="name">{t('Name')}</Label>
                                    <Input
                                        id="name"
                                        name="name"
                                        defaultValue={player.name}
                                        required
                                        autoComplete="off"
                                    />
                                    <InputError message={errors.name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="alias">
                                        {t('Alias in the kicker Manager')}
                                    </Label>
                                    <Input
                                        id="alias"
                                        name="alias"
                                        defaultValue={player.alias}
                                        required
                                        autoComplete="off"
                                    />
                                    <InputError message={errors.alias} />
                                </div>

                                <div className="flex items-center gap-4">
                                    <Button disabled={processing}>
                                        {t('Save')}
                                    </Button>
                                    <Button variant="ghost" asChild>
                                        <Link href={index()}>
                                            {t('Cancel')}
                                        </Link>
                                    </Button>
                                </div>
                            </>
                        )}
                    </Form>
                </PanelBody>
            </Panel>
        </>
    );
}

EditPlayer.layout = {
    breadcrumbs: [{ title: i18nKey('Players'), href: index() }],
};
