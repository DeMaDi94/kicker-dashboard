import { useTranslation } from '@/hooks/use-translation';
import { formatCents } from '@/lib/money';
import { cn } from '@/lib/utils';
import { show } from '@/routes/players';
import { PlayerName } from './player-name';
import type { StandingLine } from './types';

const CELL = 'px-1.5 py-2 phone:px-3';
const FIGURE = cn(CELL, 'text-right brand-figure whitespace-nowrap');
const HALF = 'max-phone:hidden';
const FIT = 'w-px whitespace-nowrap';

/*
 * STD-01 — the overall table, in the order the server ranked it; PEN-03 —
 * each player's penalty sum of the season beside the points; PEN-04 — once
 * the season has an interim settlement, that sum split into „Hinrunde“ and
 * „Rückrunde“ under one „Strafen“ heading. On a phone the two halves fold
 * into the total's cell, so the table keeps to the screen (D7).
 */
export function StandingsTable({
    seasonId,
    rows,
    split,
}: {
    seasonId: number;
    rows: StandingLine[];
    split: boolean;
}) {
    const { t, locale } = useTranslation();
    const euros = (cents: number | null) =>
        cents === null ? '–' : formatCents(cents, locale);

    if (rows.length === 0) {
        return (
            <p className="px-4 py-8 text-center text-sm text-brand-muted">
                {t('This season has no players yet.')}
            </p>
        );
    }

    return (
        <div className="relative overflow-x-auto">
            <table className="w-full text-sm">
                <thead className="border-b border-brand-line-soft text-brand-muted">
                    <tr>
                        <th
                            scope="col"
                            rowSpan={split ? 2 : 1}
                            className={cn(CELL, FIT, 'text-right font-medium')}
                        >
                            {t('Place')}
                        </th>
                        <th
                            scope="col"
                            rowSpan={split ? 2 : 1}
                            className={cn(CELL, 'w-full text-left font-medium')}
                        >
                            {t('Player')}
                        </th>
                        <th
                            scope="col"
                            rowSpan={split ? 2 : 1}
                            className={cn(CELL, 'text-right font-medium')}
                        >
                            {t('Points')}
                        </th>
                        <th
                            scope={split ? 'colgroup' : 'col'}
                            colSpan={split ? 3 : 1}
                            className={cn(
                                CELL,
                                'text-right font-medium',
                                split &&
                                    'phone:border-b phone:border-brand-line-soft phone:text-center',
                            )}
                        >
                            {t('Penalties')}
                        </th>
                    </tr>
                    {split && (
                        <tr className={HALF}>
                            <th
                                scope="col"
                                className={cn(CELL, 'text-right font-medium')}
                            >
                                {t('First half')}
                            </th>
                            <th
                                scope="col"
                                className={cn(CELL, 'text-right font-medium')}
                            >
                                {t('Second half')}
                            </th>
                            <th
                                scope="col"
                                className={cn(CELL, 'text-right font-medium')}
                            >
                                {t('Total')}
                            </th>
                        </tr>
                    )}
                </thead>
                <tbody>
                    {rows.map((row) => (
                        <tr
                            key={row.playerId}
                            className="border-b border-brand-line-soft last:border-0"
                        >
                            <td
                                className={cn(
                                    CELL,
                                    FIT,
                                    'text-right brand-figure',
                                )}
                            >
                                {row.place}.
                            </td>
                            <td className={CELL}>
                                <PlayerName
                                    name={row.name}
                                    alias={row.alias}
                                    href={show(row.playerId, {
                                        query: { season: seasonId },
                                    })}
                                />
                            </td>
                            <td className={cn(CELL, 'text-right brand-figure')}>
                                {row.points}
                            </td>
                            {split && (
                                <>
                                    <td className={cn(FIGURE, HALF)}>
                                        {euros(row.firstHalfPenaltyCents)}
                                    </td>
                                    <td className={cn(FIGURE, HALF)}>
                                        {euros(row.secondHalfPenaltyCents)}
                                    </td>
                                </>
                            )}
                            <td
                                className={cn(
                                    FIGURE,
                                    split && 'font-semibold text-brand-ink',
                                )}
                            >
                                {euros(row.penaltyCents)}
                                {split && (
                                    <span className="mt-0.5 flex flex-col text-[11px] font-normal whitespace-normal text-brand-muted phone:hidden">
                                        <span>
                                            {t('First half')}{' '}
                                            {euros(row.firstHalfPenaltyCents)}
                                        </span>
                                        <span>
                                            {t('Second half')}{' '}
                                            {euros(row.secondHalfPenaltyCents)}
                                        </span>
                                    </span>
                                )}
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
