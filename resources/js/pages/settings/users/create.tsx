import { Form, Head, Link, usePage } from '@inertiajs/react';
import StoreUserController from '@/actions/App/Http/Users/StoreUser/StoreUserController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { RoleSelect } from '@/features/users/role-select';
import type { Role } from '@/features/users/types';
import { useTranslation } from '@/hooks/use-translation';
import { i18nKey } from '@/lib/i18n';
import { useState } from 'react';
import { create, index } from '@/routes/users';

type CreateUserProps = {
    roles: Role[];
    passwordRules: string;
};

type PasswordSetup = 'invitation' | 'password';

/*
 * B14 — the admin names the account and its role; the new user gets an
 * invitation to choose a password. D15 — or the admin sets the password here;
 * the invitation stays pre-selected. D16 — while outgoing mail is switched
 * off, the invitation cannot be chosen and setting the password is.
 */
export default function CreateUser({ roles, passwordRules }: CreateUserProps) {
    const { t } = useTranslation();
    const { mailEnabled } = usePage().props;
    const [passwordSetup, setPasswordSetup] = useState<PasswordSetup>(
        mailEnabled ? 'invitation' : 'password',
    );

    return (
        <>
            <Head title={t('Create user')} />

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title={t('Create user')}
                    description={t(
                        'Name the account, give it a role and choose how its password is set.',
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

                            <fieldset className="grid gap-2">
                                <legend className="mb-2 text-sm font-medium">
                                    {t('Password')}
                                </legend>
                                <PasswordSetupOption
                                    value="invitation"
                                    checked={passwordSetup === 'invitation'}
                                    disabled={!mailEnabled}
                                    onSelect={setPasswordSetup}
                                    label={t('Send invitation')}
                                    hint={
                                        mailEnabled
                                            ? t(
                                                  'The new user receives an email with a link to choose a password.',
                                              )
                                            : t(
                                                  'Email is switched off, so no invitation can be sent.',
                                              )
                                    }
                                />
                                <PasswordSetupOption
                                    value="password"
                                    checked={passwordSetup === 'password'}
                                    onSelect={setPasswordSetup}
                                    label={t('Set password now')}
                                    hint={t(
                                        'No email is sent. Pass the password on to the user yourself.',
                                    )}
                                />
                                <InputError message={errors.password_setup} />
                            </fieldset>

                            {passwordSetup === 'password' && (
                                <>
                                    <div className="grid gap-2">
                                        <Label htmlFor="password">
                                            {t('Password')}
                                        </Label>
                                        <PasswordInput
                                            id="password"
                                            name="password"
                                            required
                                            autoComplete="new-password"
                                            placeholder={t('Password')}
                                            passwordrules={passwordRules}
                                        />
                                        <InputError message={errors.password} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="password_confirmation">
                                            {t('Confirm password')}
                                        </Label>
                                        <PasswordInput
                                            id="password_confirmation"
                                            name="password_confirmation"
                                            required
                                            autoComplete="new-password"
                                            placeholder={t('Confirm password')}
                                            passwordrules={passwordRules}
                                        />
                                        <InputError
                                            message={
                                                errors.password_confirmation
                                            }
                                        />
                                    </div>
                                </>
                            )}

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

function PasswordSetupOption({
    value,
    checked,
    disabled = false,
    onSelect,
    label,
    hint,
}: {
    value: PasswordSetup;
    checked: boolean;
    disabled?: boolean;
    onSelect: (value: PasswordSetup) => void;
    label: string;
    hint: string;
}) {
    return (
        <label className="flex cursor-pointer items-start gap-3 rounded-brand border border-brand-line p-3 has-checked:border-brand-accent has-checked:bg-brand-accent-wash has-disabled:cursor-not-allowed has-disabled:opacity-60">
            <input
                type="radio"
                name="password_setup"
                value={value}
                checked={checked}
                disabled={disabled}
                onChange={() => onSelect(value)}
                className="mt-1 accent-brand-accent"
            />
            <span className="grid gap-0.5">
                <span className="text-sm font-medium">{label}</span>
                <span className="text-sm text-brand-muted">{hint}</span>
            </span>
        </label>
    );
}

CreateUser.layout = {
    breadcrumbs: [
        { title: i18nKey('Users'), href: index() },
        { title: i18nKey('Create user'), href: create() },
    ],
};
