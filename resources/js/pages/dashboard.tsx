import { Head } from '@inertiajs/react';
import { PageTitle } from '@/components/core/page-title';
import { Panel } from '@/components/core/panel';
import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import { useTranslation } from '@/hooks/use-translation';
import { i18nKey } from '@/lib/i18n';
import { dashboard } from '@/routes';

/*
 * The blueprint's only screen: the page a signed-in user lands on. The
 * panels are placeholders in the house style until a product gives the
 * dashboard its requirements.
 */
export default function Dashboard() {
    const { t } = useTranslation();

    return (
        <>
            <Head title={t('Dashboard')} />

            <PageTitle title={t('Dashboard')} />

            <div className="flex flex-col gap-4">
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    {[1, 2, 3].map((each) => (
                        <Panel
                            key={each}
                            className="relative aspect-video overflow-hidden"
                        >
                            <PlaceholderPattern className="absolute inset-0 size-full stroke-brand-line" />
                        </Panel>
                    ))}
                </div>
                <Panel className="relative min-h-[60vh] overflow-hidden">
                    <PlaceholderPattern className="absolute inset-0 size-full stroke-brand-line" />
                </Panel>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: i18nKey('Dashboard'),
            href: dashboard(),
        },
    ],
};
