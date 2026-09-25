// Components
import { Form, Head, usePage } from '@inertiajs/react';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { logout } from '@/routes';
import { send } from '@/routes/verification';
import { useTranslation } from '@/hooks/use-translation';
import { i18nKey } from '@/lib/i18n';

export default function VerifyEmail({ status }: { status?: string }) {
    const { t } = useTranslation();
    const { mailEnabled } = usePage().props;

    return (
        <>
            <Head title={t('Email verification')} />

            {status === 'verification-link-sent' && (
                <div className="mb-4 text-center text-sm font-medium text-brand-success">
                    {t(
                        'A new verification link has been sent to the email address you provided during registration.',
                    )}
                </div>
            )}

            <Form {...send.form()} className="space-y-6 text-center">
                {({ processing }) => (
                    <>
                        {/* D16 — no verification mail while outgoing mail is switched off. */}
                        <Button
                            disabled={processing || !mailEnabled}
                            variant="secondary"
                        >
                            {processing && <Spinner />}
                            {t('Resend verification email')}
                        </Button>

                        {!mailEnabled && (
                            <p className="text-sm text-brand-muted">
                                {t(
                                    'Email is switched off, so no link can be sent.',
                                )}
                            </p>
                        )}

                        <TextLink
                            href={logout()}
                            className="mx-auto block text-sm"
                        >
                            {t('Log out')}
                        </TextLink>
                    </>
                )}
            </Form>
        </>
    );
}

VerifyEmail.layout = {
    title: i18nKey('Email verification'),
    description: i18nKey(
        'Please verify your email address by clicking on the link we just emailed to you.',
    ),
};
