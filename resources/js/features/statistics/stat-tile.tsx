import type { ReactNode } from 'react';

/*
 * One figure with its label — the form a single headline number takes
 * instead of a chart (STAT-03, STAT-07, STAT-08).
 */
export function StatTile({
    label,
    value,
    detail,
}: {
    label: string;
    value: ReactNode;
    detail?: ReactNode;
}) {
    return (
        <div className="flex min-w-0 flex-col gap-1 rounded-brand border border-brand-line-soft bg-brand-card p-3">
            <span className="brand-label text-brand-label">{label}</span>
            <span className="brand-figure text-xl font-semibold text-brand-ink">
                {value}
            </span>
            {detail && (
                <span className="text-xs text-brand-muted">{detail}</span>
            )}
        </div>
    );
}
