import { useTranslation } from '@/hooks/use-translation';
import { formatNumber } from '@/lib/number';
import { pageLabel } from './page-label';
import type { VisitStatistics } from './types';

/*
 * VIS-06 — the period's visits per public page, the most visited first; all
 * four pages, one nobody called with 0 (D14).
 */
export function PageTable({ pages }: { pages: VisitStatistics['pages'] }) {
    const { t, locale } = useTranslation();

    return (
        <table className="w-full text-sm">
            <thead className="border-b border-brand-line-soft text-brand-muted">
                <tr>
                    <th
                        scope="col"
                        className="px-2 py-2 text-left font-medium phone:px-3"
                    >
                        {t('Page')}
                    </th>
                    <th
                        scope="col"
                        className="px-2 py-2 text-right font-medium phone:px-3"
                    >
                        {t('Visits')}
                    </th>
                </tr>
            </thead>
            <tbody>
                {pages.map((each) => (
                    <tr
                        key={each.page}
                        className="border-b border-brand-line-soft last:border-0"
                    >
                        <td className="px-2 py-2 phone:px-3">
                            {pageLabel(each.page, t)}
                        </td>
                        <td className="px-2 py-2 text-right brand-figure phone:px-3">
                            {formatNumber(each.visits, locale)}
                        </td>
                    </tr>
                ))}
            </tbody>
        </table>
    );
}
