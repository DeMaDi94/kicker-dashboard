import { Form, Head, Link } from '@inertiajs/react';
import StoreUserController from '@/actions/App/Http/Users/StoreUser/StoreUserController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { RoleSelect } from '@/features/users/role-select';
import type { Role } from '@/features/users/types';
import { useTranslation } from '@/hooks/use-translation';
import { i18nKey } from '@/lib/i18n';
import { create, index } from '@/routes/users';

type CreateUserProps = {
    roles: Role[];
};

/*
 * B14 — the admin names the account and its role; the new user gets an
 * invitation to choose a password.
 */
export default function CreateUser({ roles }: CreateUserProps) {
    const { t } = useTranslation();

    return (
        <>
            <Head title={t('Create user')} />

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title={t('Create user')}
                    description={t(
                        'The new user receives an email with a link to choose a password.',
                    )}
                />

                <Form {...StoreUserController.form()} className="space-y-6">
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">{t('Name')}</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    required
                                    autoComplete="off"
                                    placeholder={t('Full name')}
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">
                                    {t('Email address')}
                                </Label>
                                <Input
                                    id="email"
                                    name="email"
                                    type="email"
                                    required
                                    autoComplete="off"
                                    placeholder={t('Email address')}
                                />
                                <InputError message={errors.email} />
                            </div>

                            <RoleSelect roles={roles} error={errors.role} />

                            <div className="flex items-center gap-4">
                                <Button disabled={processing}>
                                    {t('Create user')}
                                </Button>
                                <Button variant="ghost" asChild>
                                    <Link href={index()}>{t('Cancel')}</Link>
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

CreateUser.layout = {
    breadcrumbs: [
        { title: i18nKey('Users'), href: index() },
        { title: i18nKey('Create user'), href: create() },
    ],
};
