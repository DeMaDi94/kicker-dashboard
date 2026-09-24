import { useTranslation } from '@/hooks/use-translation';
import { formatCents } from '@/lib/money';
import { PlayerName } from './player-name';
import type { StandingLine } from './types';

/*
 * STD-01 — the overall table, in the order the server ranked it; PEN-03 —
 * each player's penalty sum of the season beside the points.
 */
export function StandingsTable({ rows }: { rows: StandingLine[] }) {
    const { t, locale } = useTranslation();

    if (rows.length === 0) {
        return (
            <p className="px-4 py-8 text-center text-sm text-brand-muted">
                {t('This season has no players yet.')}
            </p>
        );
    }

    return (
        <div className="overflow-x-auto">
            <table className="w-full text-sm">
                <thead className="border-b border-brand-line-soft text-brand-muted">
                    <tr>
                        <th
                            scope="col"
                            className="px-2 py-2 text-right font-medium phone:px-3"
                        >
                            {t('Place')}
                        </th>
                        <th
                            scope="col"
                            className="px-2 py-2 text-left font-medium phone:px-3"
                        >
                            {t('Player')}
                        </th>
                        <th
                            scope="col"
                            className="px-2 py-2 text-right font-medium phone:px-3"
                        >
                            {t('Points')}
                        </th>
                        <th
                            scope="col"
                            className="px-2 py-2 text-right font-medium phone:px-3"
                        >
                            {t('Penalties')}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {rows.map((row) => (
                        <tr
                            key={row.playerId}
                            className="border-b border-brand-line-soft last:border-0"
                        >
                            <td className="px-2 py-2 text-right brand-figure phone:px-3">
                                {row.place}.
                            </td>
                            <td className="px-2 py-2 phone:px-3">
                                <PlayerName name={row.name} alias={row.alias} />
                            </td>
                            <td className="px-2 py-2 text-right brand-figure phone:px-3">
                                {row.points}
                            </td>
                            <td className="px-2 py-2 text-right brand-figure phone:px-3">
                                {formatCents(row.penaltyCents, locale)}
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
