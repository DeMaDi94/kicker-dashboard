import { Link } from '@inertiajs/react';
import { useTranslation } from '@/hooks/use-translation';
import { formatCents } from '@/lib/money';
import { show } from '@/routes/players';
import type { PenaltyBox as PenaltyBoxData } from './types';

/*
 * STAT-12 — the season's penalty box: what went in (split as PEN-04 splits
 * it) and who paid most first.
 */
export function PenaltyBox({
    seasonId,
    box,
}: {
    seasonId: number;
    box: PenaltyBoxData;
}) {
    const { t, locale } = useTranslation();
    const euros = (cents: number) => formatCents(cents, locale);

    return (
        <div className="grid gap-4 p-4">
            <dl className="flex flex-wrap gap-x-8 gap-y-2">
                <div>
                    <dt className="brand-label text-brand-label">
                        {t('In the box')}
                    </dt>
                    <dd className="brand-figure text-2xl font-semibold text-brand-ink">
                        {euros(box.totalCents)}
                    </dd>
                </div>
                {box.firstHalfCents !== null &&
                    box.secondHalfCents !== null && (
                        <>
                            <div>
                                <dt className="brand-label text-brand-label">
                                    {t('First half')}
                                </dt>
                                <dd className="brand-figure text-lg font-medium text-brand-ink-soft">
                                    {euros(box.firstHalfCents)}
                                </dd>
                            </div>
                            <div>
                                <dt className="brand-label text-brand-label">
                                    {t('Second half')}
                                </dt>
                                <dd className="brand-figure text-lg font-medium text-brand-ink-soft">
                                    {euros(box.secondHalfCents)}
                                </dd>
                            </div>
                        </>
                    )}
            </dl>

            <ol className="grid gap-1 text-sm">
                {box.payers.map((payer) => (
                    <li
                        key={payer.playerId}
                        className="flex items-center justify-between gap-4 border-b border-brand-line-soft py-1.5 last:border-0"
                    >
                        <Link
                            href={show(payer.playerId, {
                                query: { season: seasonId },
                            })}
                            className="font-medium text-brand-ink underline-offset-2 hover:underline"
                        >
                            {payer.name}
                        </Link>
                        <span className="brand-figure whitespace-nowrap">
                            {euros(payer.penaltyCents)}
                        </span>
                    </li>
                ))}
            </ol>
        </div>
    );
}
