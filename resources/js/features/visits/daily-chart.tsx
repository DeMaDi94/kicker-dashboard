import {
    CartesianGrid,
    Line,
    LineChart,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';
import { AXIS_TICK, CHART, ChartTooltip } from '@/components/core/chart';
import { ChartLegend } from '@/components/core/chart-legend';
import { useTranslation } from '@/hooks/use-translation';
import { formatNumber } from '@/lib/number';
import { longDate, shortDate } from './dates';
import type { VisitDay } from './types';

/*
 * VIS-05 — the visits and the visitors of each day of the period, as two
 * lines on one axis.
 */
export function DailyChart({ days }: { days: VisitDay[] }) {
    const { t, locale } = useTranslation();

    return (
        <div>
            <ChartLegend
                items={[
                    { label: t('Visits'), color: CHART.one },
                    { label: t('Visitors'), color: CHART.two },
                ]}
            />
            <div className="h-56 px-1 pb-2">
                <ResponsiveContainer width="100%" height="100%">
                    <LineChart
                        data={days}
                        margin={{ top: 12, right: 12, bottom: 0, left: 0 }}
                    >
                        <CartesianGrid vertical={false} stroke={CHART.grid} />
                        <XAxis
                            dataKey="date"
                            tick={AXIS_TICK}
                            tickLine={false}
                            axisLine={{ stroke: CHART.grid }}
                            tickFormatter={(date: string) =>
                                shortDate(date, locale)
                            }
                            minTickGap={24}
                        />
                        <YAxis
                            width={36}
                            tick={AXIS_TICK}
                            tickLine={false}
                            axisLine={false}
                            allowDecimals={false}
                        />
                        <Tooltip
                            cursor={{ stroke: CHART.grid }}
                            content={({ active, label }) => {
                                const day = days.find(
                                    (each) => each.date === label,
                                );

                                return active && day ? (
                                    <ChartTooltip
                                        title={longDate(day.date, locale)}
                                        rows={[
                                            {
                                                label: t('Visits'),
                                                value: formatNumber(
                                                    day.visits,
                                                    locale,
                                                ),
                                                color: CHART.one,
                                            },
                                            {
                                                label: t('Visitors'),
                                                value: formatNumber(
                                                    day.visitors,
                                                    locale,
                                                ),
                                                color: CHART.two,
                                            },
                                        ]}
                                    />
                                ) : null;
                            }}
                        />
                        <Line
                            dataKey="visits"
                            stroke={CHART.one}
                            strokeWidth={2}
                            dot={false}
                            isAnimationActive={false}
                        />
                        <Line
                            dataKey="visitors"
                            stroke={CHART.two}
                            strokeWidth={2}
                            dot={false}
                            isAnimationActive={false}
                        />
                    </LineChart>
                </ResponsiveContainer>
            </div>
        </div>
    );
}
