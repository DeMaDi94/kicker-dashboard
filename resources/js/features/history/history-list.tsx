import { useTranslation } from '@/hooks/use-translation';
import { formatDateTime } from '@/lib/date';
import { actionLabel, changeLabel, changeValue } from './labels';
import type { HistoryEntry } from './types';

/*
 * LOG-02 — each entry: who, when, the action on which season or player, and
 * every changed value with its old and its new value.
 */
export function HistoryList({ entries }: { entries: HistoryEntry[] }) {
    const { t, locale } = useTranslation();

    return (
        <ul>
            {entries.map((entry) => (
                <li
                    key={entry.id}
                    className="border-b border-brand-line-soft px-4 py-3 last:border-0"
                >
                    <div className="flex flex-wrap items-baseline gap-x-2 text-xs text-brand-muted">
                        <time dateTime={entry.recordedAt}>
                            {formatDateTime(entry.recordedAt, locale)}
                        </time>
                        <span aria-hidden>·</span>
                        <span className="font-medium text-brand-ink-soft">
                            {entry.userName ?? '—'}
                        </span>
                    </div>
                    <p className="mt-1 text-sm font-medium text-brand-ink">
                        {actionLabel(entry.action, t)}
                        {entry.subject !== null && ` · ${entry.subject}`}
                        {entry.matchday !== null &&
                            ` · ${t('Matchday :number', { number: entry.matchday })}`}
                    </p>
                    {entry.changes.length > 0 && (
                        <dl className="mt-1 grid grid-cols-[auto_1fr] gap-x-3 gap-y-0.5 text-sm">
                            {entry.changes.map((change, index) => (
                                <div key={index} className="contents">
                                    <dt className="text-brand-muted">
                                        {changeLabel(change, t)}
                                    </dt>
                                    <dd className="break-words whitespace-pre-line text-brand-ink-soft">
                                        {changeValue(
                                            change.field,
                                            change.old,
                                            t,
                                            locale,
                                        )}{' '}
                                        →{' '}
                                        {changeValue(
                                            change.field,
                                            change.new,
                                            t,
                                            locale,
                                        )}
                                    </dd>
                                </div>
                            ))}
                        </dl>
                    )}
                </li>
            ))}
        </ul>
    );
}
