import { Form, Head, router, setLayoutProps, usePage } from '@inertiajs/react';
import UpdateUserController from '@/actions/App/Http/Users/UpdateUser/UpdateUserController';
import { useConfirm } from '@/components/core/dialogs';
import { toast } from '@/components/core/toast';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { RoleSelect } from '@/features/users/role-select';
import type { Role, UserRow } from '@/features/users/types';
import { useTranslation } from '@/hooks/use-translation';
import { i18nKey } from '@/lib/i18n';
import { destroy, edit, index, passwordResetLink } from '@/routes/users';

type EditUserProps = {
    user: UserRow;
    roles: Role[];
    /** B15 — why this user cannot be deleted from here, if they cannot. */
    deleteRefusal: string | null;
    /** B15 — why this user cannot lose the admin role, if they cannot. */
    demoteRefusal: string | null;
};

const firstError = (errors: Record<string, string>): string | undefined =>
    Object.values(errors)[0];

/*
 * B16 — an admin changes a user's name and role and can send a password
 * reset link. The address is the user's own and is not edited here.
 */
export default function EditUser({
    user,
    roles,
    deleteRefusal,
    demoteRefusal,
}: EditUserProps) {
    const { t } = useTranslation();
    const { mailEnabled } = usePage().props;

    setLayoutProps({
        breadcrumbs: [
            { title: i18nKey('Users'), href: index() },
            { title: user.name, href: edit(user.id), verbatim: true },
        ],
    });
    const confirm = useConfirm();

    const onError = (errors: Record<string, string>) => {
        const message = firstError(errors);

        if (message) {
            toast(message, 'err');
        }
    };

    const sendResetLink = () =>
        router.post(
            passwordResetLink(user.id),
            {},
            { preserveScroll: true, onError },
        );

    const deleteUser = async () => {
        const confirmed = await confirm(
            t(
                ':name is signed out and can no longer sign in. You can restore the user later.',
                { name: user.name },
            ),
            {
                title: t('Delete user?'),
                okLabel: t('Delete user'),
                danger: true,
            },
        );

        if (confirmed) {
            router.delete(destroy(user.id), { onError });
        }
    };

    return (
        <>
            <Head title={user.name} />

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title={user.name}
                    description={user.email}
                />

                <Form
                    {...UpdateUserController.form(user.id)}
                    options={{ preserveScroll: true }}
                    className="space-y-6"
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
                                    defaultValue={user.name}
                                />
                                <InputError message={errors.name} />
                            </div>

                            <RoleSelect
                                roles={roles}
                                defaultValue={user.role}
                                lockedReason={
                                    user.role === 'admin' ? demoteRefusal : null
                                }
                                error={errors.role}
                            />

                            <Button disabled={processing}>{t('Save')}</Button>
                        </>
                    )}
                </Form>
            </div>

            <div className="space-y-4">
                <Heading
                    variant="small"
                    title={t('Password')}
                    description={t(
                        'Send the user an email with a link to choose a new password.',
                    )}
                />
                {/* D16 — no reset link while outgoing mail is switched off. */}
                <Button
                    variant="outline"
                    onClick={sendResetLink}
                    disabled={!mailEnabled}
                >
                    {t('Send password reset link')}
                </Button>
                {!mailEnabled && (
                    <p className="text-sm text-brand-muted">
                        {t('Email is switched off, so no link can be sent.')}
                    </p>
                )}
            </div>

            <div className="space-y-4">
                <Heading
                    variant="small"
                    title={t('Delete user')}
                    description={t(
                        'The user is signed out and can no longer sign in. An admin can restore them from the list of deleted users.',
                    )}
                />
                {deleteRefusal ? (
                    <p className="text-sm text-brand-muted">{deleteRefusal}</p>
                ) : (
                    <Button variant="destructive" onClick={deleteUser}>
                        {t('Delete user')}
                    </Button>
                )}
            </div>
        </>
    );
}

EditUser.layout = {
    breadcrumbs: [{ title: i18nKey('Users'), href: index() }],
};
