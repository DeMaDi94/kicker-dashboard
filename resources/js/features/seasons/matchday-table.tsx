import { useTranslation } from '@/hooks/use-translation';
import { formatCents } from '@/lib/money';
import { PlayerName } from './player-name';
import type { MatchdayBlock } from './types';

/*
 * One matchday's points, and — MD-02 — its places and penalties once every
 * player has points.
 */
export function MatchdayTable({ matchday }: { matchday: MatchdayBlock }) {
    const { t, locale } = useTranslation();

    if (!matchday.hasPoints) {
        return (
            <p className="px-4 py-8 text-center text-sm text-brand-muted">
                {t('No points entered for this matchday yet.')}
            </p>
        );
    }

    return (
        <div className="overflow-x-auto">
            {!matchday.complete && (
                <p className="px-4 pt-3 text-sm text-brand-muted">
                    {t(
                        'Places and penalties appear once every player has points.',
                    )}
                </p>
            )}
            <table className="w-full text-sm">
                <thead className="border-b border-brand-line-soft text-brand-muted">
                    <tr>
                        <th
                            scope="col"
                            className="px-3 py-2 text-right font-medium"
                        >
                            {t('Place')}
                        </th>
                        <th
                            scope="col"
                            className="px-3 py-2 text-left font-medium"
                        >
                            {t('Player')}
                        </th>
                        <th
                            scope="col"
                            className="px-3 py-2 text-right font-medium"
                        >
                            {t('Points')}
                        </th>
                        <th
                            scope="col"
                            className="px-3 py-2 text-right font-medium"
                        >
                            {t('Penalty')}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {matchday.rows.map((row) => (
                        <tr
                            key={row.playerId}
                            className="border-b border-brand-line-soft last:border-0"
                        >
                            <td className="px-3 py-2 text-right brand-figure">
                                {row.place === null ? '–' : `${row.place}.`}
                            </td>
                            <td className="px-3 py-2">
                                <PlayerName name={row.name} alias={row.alias} />
                            </td>
                            <td className="px-3 py-2 text-right brand-figure">
                                {row.points ?? '–'}
                            </td>
                            <td className="px-3 py-2 text-right brand-figure">
                                {row.penaltyCents === null
                                    ? '–'
                                    : formatCents(row.penaltyCents, locale)}
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
