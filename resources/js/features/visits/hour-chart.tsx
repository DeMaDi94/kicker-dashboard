import {
    Bar,
    BarChart,
    CartesianGrid,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';
import { AXIS_TICK, CHART, ChartTooltip } from '@/components/core/chart';
import { useTranslation } from '@/hooks/use-translation';
import { formatNumber } from '@/lib/number';

/*
 * VIS-06 — the period's visits per hour of the day, 0 to 23, as bars. One
 * named series, so no legend (D10).
 */
export function HourChart({ hours }: { hours: number[] }) {
    const { t, locale } = useTranslation();
    const data = hours.map((visits, hour) => ({ hour, visits }));

    return (
        <div className="h-48 px-1 pt-3 pb-2">
            <ResponsiveContainer width="100%" height="100%">
                <BarChart
                    data={data}
                    margin={{ top: 12, right: 12, bottom: 0, left: 0 }}
                >
                    <CartesianGrid vertical={false} stroke={CHART.grid} />
                    <XAxis
                        dataKey="hour"
                        tick={AXIS_TICK}
                        tickLine={false}
                        axisLine={{ stroke: CHART.grid }}
                        minTickGap={8}
                    />
                    <YAxis
                        width={36}
                        tick={AXIS_TICK}
                        tickLine={false}
                        axisLine={false}
                        allowDecimals={false}
                    />
                    <Tooltip
                        cursor={{ fill: CHART.grid }}
                        content={({ active, label }) => {
                            const hour = data.find(
                                (each) => each.hour === label,
                            );

                            return active && hour ? (
                                <ChartTooltip
                                    title={t(':hour o’clock', {
                                        hour: hour.hour,
                                    })}
                                    rows={[
                                        {
                                            label: t('Visits'),
                                            value: formatNumber(
                                                hour.visits,
                                                locale,
                                            ),
                                            color: CHART.one,
                                        },
                                    ]}
                                />
                            ) : null;
                        }}
                    />
                    <Bar
                        dataKey="visits"
                        fill={CHART.one}
                        isAnimationActive={false}
                    />
                </BarChart>
            </ResponsiveContainer>
        </div>
    );
}
