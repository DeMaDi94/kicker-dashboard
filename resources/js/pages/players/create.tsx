import { Form, Head, Link } from '@inertiajs/react';
import StorePlayerController from '@/actions/App/Http/Players/StorePlayer/StorePlayerController';
import { PageTitle } from '@/components/core/page-title';
import { Panel, PanelBody } from '@/components/core/panel';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useTranslation } from '@/hooks/use-translation';
import { i18nKey } from '@/lib/i18n';
import { create, index } from '@/routes/players';

/* PLY-01 — a player: a name and the alias from the kicker Manager. */
export default function CreatePlayer() {
    const { t } = useTranslation();

    return (
        <>
            <Head title={t('Create player')} />

            <PageTitle title={t('Create player')} />

            <Panel>
                <PanelBody>
                    <Form
                        {...StorePlayerController.form()}
                        className="grid max-w-xl gap-6"
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="name">{t('Name')}</Label>
                                    <Input
                                        id="name"
                                        name="name"
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
                                        required
                                        autoComplete="off"
                                    />
                                    <InputError message={errors.alias} />
                                </div>

                                <div className="flex items-center gap-4">
                                    <Button disabled={processing}>
                                        {t('Create player')}
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

CreatePlayer.layout = {
    breadcrumbs: [
        { title: i18nKey('Players'), href: index() },
        { title: i18nKey('Create player'), href: create() },
    ],
};
