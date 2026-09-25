import { Form, Head } from '@inertiajs/react';
import { useState } from 'react';
import UpdateMailSettingsController from '@/actions/App/Http/Users/UpdateMailSettings/UpdateMailSettingsController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { useTranslation } from '@/hooks/use-translation';
import { i18nKey } from '@/lib/i18n';
import { edit } from '@/routes/mail-settings';

type MailSettingsProps = {
    mailEnabled: boolean;
};

/*
 * D16 — one switch for every mail the app sends: the invitation, the
 * password reset and the verification of an address.
 */
export default function MailSettings({ mailEnabled }: MailSettingsProps) {
    const { t } = useTranslation();
    const [enabled, setEnabled] = useState(mailEnabled);

    return (
        <>
            <Head title={t('Email')} />

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title={t('Email')}
                    description={t(
                        'Invitations, password reset links and address verification are sent by email. Switch it on once sending mail is set up.',
                    )}
                />

                <Form
                    {...UpdateMailSettingsController.form()}
                    options={{ preserveScroll: true }}
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <div className="flex items-center gap-3">
                                    <Checkbox
                                        id="mail_enabled"
                                        checked={enabled}
                                        onCheckedChange={(checked) =>
                                            setEnabled(checked === true)
                                        }
                                    />
                                    <Label htmlFor="mail_enabled">
                                        {t('Send emails')}
                                    </Label>
                                </div>
                                <input
                                    type="hidden"
                                    name="mail_enabled"
                                    value={enabled ? '1' : '0'}
                                />
                                <InputError message={errors.mail_enabled} />
                            </div>

                            <Button disabled={processing}>{t('Save')}</Button>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

MailSettings.layout = {
    breadcrumbs: [{ title: i18nKey('Email'), href: edit() }],
};
