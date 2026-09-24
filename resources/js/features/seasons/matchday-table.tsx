import { useTranslation } from '@/hooks/use-translation';
import { formatCents } from '@/lib/money';
import { formatNumber } from '@/lib/number';
import { show } from '@/routes/players';
import { PlayerName } from './player-name';
import type { MatchdayBlock } from './types';

/*
 * One matchday's points, and — MD-02 — its places and penalties once every
 * player has points.
 */
export function MatchdayTable({
    seasonId,
    matchday,
}: {
    seasonId: number;
    matchday: MatchdayBlock;
}) {
    const { t, locale } = useTranslation();
    const highlights = matchday.highlights;
    const names = (players: { name: string }[]) =>
        players.map((player) => player.name).join(', ');

    if (!matchday.hasPoints) {
        return (
            <p className="px-4 py-8 text-center text-sm text-brand-muted">
                {t('No points entered for this matchday yet.')}
            </p>
        );
    }

    return (
        <div className="relative overflow-x-auto">
            {/* STAT-11 — the day's winners, „Rote Laterne“ and average; STAT-13 — the money into the box. */}
            {highlights && (
                <dl className="grid grid-cols-2 gap-2 border-b border-brand-line-soft px-4 py-3 text-sm phone:grid-cols-4">
                    <div className="min-w-0">
                        <dt className="brand-label text-brand-label">
                            {t('Matchday winner')}
                        </dt>
                        <dd className="truncate font-medium text-brand-ink">
                            {names(highlights.winners)}
                        </dd>
                    </div>
                    <div className="min-w-0">
                        <dt className="brand-label text-brand-label">
                            {t('Rote Laterne')}
                        </dt>
                        <dd className="truncate font-medium text-brand-ink">
                            {names(highlights.lanterns)}
                        </dd>
                    </div>
                    <div className="min-w-0">
                        <dt className="brand-label text-brand-label">
                            {t('League average')}
                        </dt>
                        <dd className="brand-figure font-medium text-brand-ink">
                            {formatNumber(highlights.average, locale, 1)}
                        </dd>
                    </div>
                    <div className="min-w-0">
                        <dt className="brand-label text-brand-label">
                            {t('Into the box')}
                        </dt>
                        <dd className="brand-figure font-medium text-brand-ink">
                            {formatCents(highlights.penaltyCents, locale)}
                        </dd>
                    </div>
                </dl>
            )}
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
                            <td className="px-2 py-2 text-right brand-figure phone:px-3">
                                {row.place === null ? '–' : `${row.place}.`}
                            </td>
                            <td className="px-1.5 py-2 phone:px-3">
                                <PlayerName
                                    name={row.name}
                                    alias={row.alias}
                                    href={show(row.playerId, {
                                        query: { season: seasonId },
                                    })}
                                />
                            </td>
                            <td className="px-2 py-2 text-right brand-figure phone:px-3">
                                {row.points ?? '–'}
                            </td>
                            <td className="px-2 py-2 text-right brand-figure phone:px-3">
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
