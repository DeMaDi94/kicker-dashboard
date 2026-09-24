import { Button } from '@/components/ui/button';
import { useTranslation } from '@/hooks/use-translation';

/*
 * The pager under a paged list: „‹ Previous · Page N of M · Next ›“. The
 * buttons at either end are disabled rather than hidden, so the line keeps
 * its shape. Where the page lives is the caller's (`useListQuery`).
 */
export function ListPager({
    page,
    lastPage,
    onPageChange,
}: {
    page: number;
    lastPage: number;
    onPageChange: (page: number) => void;
}) {
    const { t } = useTranslation();

    return (
        <nav className="flex items-center justify-center gap-2 text-sm text-brand-muted">
            <Button
                variant="ghost"
                size="sm"
                disabled={page <= 1}
                onClick={() => onPageChange(page - 1)}
            >
                <span aria-hidden="true">‹</span> {t('Previous')}
            </Button>
            <span aria-hidden="true">·</span>
            <span>{t('Page :page of :last', { page, last: lastPage })}</span>
            <span aria-hidden="true">·</span>
            <Button
                variant="ghost"
                size="sm"
                disabled={page >= lastPage}
                onClick={() => onPageChange(page + 1)}
            >
                {t('Next')} <span aria-hidden="true">›</span>
            </Button>
        </nav>
    );
}
