import {
    Bar,
    CartesianGrid,
    ComposedChart,
    Line,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';
import { useTranslation } from '@/hooks/use-translation';
import { formatNumber } from '@/lib/number';
import { ChartLegend } from './chart-legend';
import { AXIS_TICK, CHART, ChartTooltip } from './chart-tooltip';
import type { MatchdayLine } from './types';

/*
 * STAT-04 — the player's points per matchday as bars, the league average of
 * each matchday as a dashed reference line on the same axis.
 */
export function PointsChart({ lines }: { lines: MatchdayLine[] }) {
    const { t, locale } = useTranslation();

    return (
        <div>
            <ChartLegend
                items={[
                    { label: t('Points'), color: CHART.one },
                    {
                        label: t('League average'),
                        color: CHART.reference,
                        dashed: true,
                    },
                ]}
            />
            <div className="h-56 px-1 pb-2">
                <ResponsiveContainer width="100%" height="100%">
                    <ComposedChart
                        data={lines}
                        margin={{ top: 12, right: 12, bottom: 0, left: 0 }}
                    >
                        <CartesianGrid vertical={false} stroke={CHART.grid} />
                        <XAxis
                            dataKey="matchday"
                            tick={AXIS_TICK}
                            tickLine={false}
                            axisLine={{ stroke: CHART.grid }}
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
                                const line = lines.find(
                                    (each) => each.matchday === label,
                                );

                                return active && line ? (
                                    <ChartTooltip
                                        title={t('Matchday :number', {
                                            number: line.matchday,
                                        })}
                                        rows={[
                                            {
                                                label: t('Points'),
                                                value: line.points,
                                                color: CHART.one,
                                            },
                                            {
                                                label: t('League average'),
                                                value: formatNumber(
                                                    line.leagueAverage,
                                                    locale,
                                                    1,
                                                ),
                                                color: CHART.reference,
                                                dashed: true,
                                            },
                                        ]}
                                    />
                                ) : null;
                            }}
                        />
                        <Bar
                            dataKey="points"
                            fill={CHART.one}
                            radius={[4, 4, 0, 0]}
                            maxBarSize={24}
                            isAnimationActive={false}
                        />
                        <Line
                            dataKey="leagueAverage"
                            stroke={CHART.reference}
                            strokeWidth={2}
                            strokeDasharray="4 3"
                            dot={false}
                            isAnimationActive={false}
                        />
                    </ComposedChart>
                </ResponsiveContainer>
            </div>
        </div>
    );
}
