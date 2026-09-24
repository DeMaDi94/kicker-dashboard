import type { ReactNode } from 'react';

/*
 * The tooltip card every graph shows on hover or tap: the matchday, then one
 * row per value with its series swatch. Text keeps the text ink; only the
 * swatch carries the series colour.
 */
export function ChartTooltip({
    title,
    rows,
}: {
    title: string;
    rows: {
        label: string;
        value: ReactNode;
        color: string;
        dashed?: boolean;
    }[];
}) {
    return (
        <div className="min-w-36 rounded-brand border border-brand-line bg-brand-card px-3 py-2 text-xs text-brand-ink">
            <p className="mb-1 font-semibold">{title}</p>
            {rows.map((row) => (
                <p key={row.label} className="flex items-center gap-2">
                    <span
                        aria-hidden
                        className="inline-block h-0.5 w-3"
                        style={{
                            background: row.dashed ? 'transparent' : row.color,
                            borderTop: row.dashed
                                ? `2px dashed ${row.color}`
                                : undefined,
                        }}
                    />
                    <span className="text-brand-muted">{row.label}</span>
                    <span className="ml-auto brand-figure font-medium">
                        {row.value}
                    </span>
                </p>
            ))}
        </div>
    );
}

/* D10 — the graph colours (the shadcn chart variables in resources/css/app.css), for the SVG attributes Recharts takes. */
export const CHART = {
    one: 'var(--chart-1)',
    two: 'var(--chart-2)',
    reference: 'var(--chart-4)',
    grid: 'var(--color-brand-line-soft)',
    axis: 'var(--color-brand-muted)',
} as const;

export const AXIS_TICK = { fill: CHART.axis, fontSize: 11 } as const;
