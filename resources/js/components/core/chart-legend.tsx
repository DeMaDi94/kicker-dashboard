/*
 * The legend above a graph with two series (STAT-04, STAT-09, VIS-05), so identity is
 * never carried by colour alone.
 */
export function ChartLegend({
    items,
}: {
    items: { label: string; color: string; dashed?: boolean }[];
}) {
    return (
        <ul className="flex flex-wrap gap-x-4 gap-y-1 px-4 pt-3 text-xs text-brand-ink-soft">
            {items.map((item) => (
                <li key={item.label} className="flex items-center gap-1.5">
                    <span
                        aria-hidden
                        className="inline-block h-0 w-4 border-t-2"
                        style={{
                            borderColor: item.color,
                            borderStyle: item.dashed ? 'dashed' : 'solid',
                        }}
                    />
                    {item.label}
                </li>
            ))}
        </ul>
    );
}
