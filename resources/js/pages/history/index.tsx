import { Head, router } from '@inertiajs/react';
import { ListPager } from '@/components/core/list-pager';
import { PageTitle } from '@/components/core/page-title';
import { Panel } from '@/components/core/panel';
import { HistoryList } from '@/features/history/history-list';
import type { HistoryEntry } from '@/features/history/types';
import { useTranslation } from '@/hooks/use-translation';
import { i18nKey } from '@/lib/i18n';
import { index } from '@/routes/history';

type HistoryIndexProps = {
    entries: HistoryEntry[];
    pagination: { page: number; lastPage: number; total: number };
};

/* LOG-03 — the history, for admins only, the newest entry first. */
export default function HistoryIndex({
    entries,
    pagination,
}: HistoryIndexProps) {
    const { t } = useTranslation();

    return (
        <>
            <Head title={t('History')} />

            <PageTitle title={t('History')} />

            <Panel>
                {entries.length === 0 ? (
                    <p className="px-4 py-8 text-center text-sm text-brand-muted">
                        {t('Nothing has been changed yet.')}
                    </p>
                ) : (
                    <HistoryList entries={entries} />
                )}
                {pagination.lastPage > 1 && (
                    <ListPager
                        page={pagination.page}
                        lastPage={pagination.lastPage}
                        onPageChange={(page) =>
                            router.visit(index({ query: { page } }))
                        }
                    />
                )}
            </Panel>
        </>
    );
}

HistoryIndex.layout = {
    breadcrumbs: [{ title: i18nKey('History'), href: index() }],
};
