import { useTranslation } from '@/hooks/use-translation';
import { formatCents } from '@/lib/money';
import { cn } from '@/lib/utils';
import { show } from '@/routes/players';
import { BoxChart } from './box-chart';
import { PlayerName } from './player-name';
import { ROW_LINK, visitRow } from './row-link';
import type { PenaltyBox as PenaltyBoxData } from './types';

/*
 * STAT-12 — the season's penalty box: what went in (split as PEN-04 splits
 * it) and who paid most first. STAT-14 — and the graph of the money per
 * matchday and the balance, the interim settlement marked.
 */
export function PenaltyBox({
    seasonId,
    box,
    settlementMatchday,
}: {
    seasonId: number;
    box: PenaltyBoxData;
    settlementMatchday: number | null;
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

            {box.matchdays.length > 0 && (
                <BoxChart
                    matchdays={box.matchdays}
                    settlementMatchday={settlementMatchday}
                />
            )}

            <ol className="grid gap-1 text-sm">
                {box.payers.map((payer) => (
                    <li
                        key={payer.playerId}
                        className={cn(
                            '-mx-2 flex items-center justify-between gap-4 border-b border-brand-line-soft px-2 py-1.5 last:border-0',
                            ROW_LINK,
                        )}
                        onClick={visitRow(
                            show(payer.playerId, {
                                query: { season: seasonId },
                            }),
                        )}
                    >
                        <PlayerName
                            name={payer.name}
                            alias={payer.alias}
                            href={show(payer.playerId, {
                                query: { season: seasonId },
                            })}
                        />
                        <span className="brand-figure whitespace-nowrap">
                            {euros(payer.penaltyCents)}
                        </span>
                    </li>
                ))}
            </ol>
        </div>
    );
}
