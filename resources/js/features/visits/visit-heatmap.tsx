import { CHART } from '@/components/core/chart';
import { useTranslation } from '@/hooks/use-translation';
import { formatNumber } from '@/lib/number';
import { weekdays } from './dates';

/*
 * VIS-06 — the period's visits by weekday (Monday to Sunday) and hour (0 to
 * 23). A cell's shade is its share of the busiest cell, in the first series
 * colour (D10); the count is in the cell's title and read out to a screen
 * reader, so the shade never carries it alone. Too wide for a phone, the
 * table scrolls inside its panel (D7).
 */
export function VisitHeatmap({ heatmap }: { heatmap: number[][] }) {
    const { t, locale } = useTranslation();
    const days = weekdays(locale);
    const busiest = Math.max(1, ...heatmap.flat());

    return (
        <div className="relative overflow-x-auto p-3">
            <table className="w-full border-separate border-spacing-0.5 text-[11px] text-brand-muted">
                <thead>
                    <tr>
                        <th scope="col">
                            <span className="sr-only">{t('Weekday')}</span>
                        </th>
                        {heatmap[0]?.map((_, hour) => (
                            <th
                                key={hour}
                                scope="col"
                                className="min-w-5 font-normal"
                            >
                                {hour}
                            </th>
                        ))}
                    </tr>
                </thead>
                <tbody>
                    {heatmap.map((hours, weekday) => (
                        <tr key={weekday}>
                            <th
                                scope="row"
                                className="pr-2 text-left font-normal"
                            >
                                {days[weekday]}
                            </th>
                            {hours.map((visits, hour) => {
                                const label = t(
                                    ':weekday, :hour o’clock: :count visits',
                                    {
                                        weekday: days[weekday] ?? '',
                                        hour,
                                        count: formatNumber(visits, locale),
                                    },
                                );

                                return (
                                    <td
                                        key={hour}
                                        title={label}
                                        className="h-6 rounded-brand border border-brand-line-faint"
                                        style={{
                                            background:
                                                visits === 0
                                                    ? undefined
                                                    : `color-mix(in oklab, ${CHART.one} ${Math.round((visits / busiest) * 100)}%, transparent)`,
                                        }}
                                    >
                                        <span className="sr-only">{label}</span>
                                    </td>
                                );
                            })}
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
