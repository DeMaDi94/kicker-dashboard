import {
    CartesianGrid,
    Line,
    LineChart,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';
import { useTranslation } from '@/hooks/use-translation';
import { ChartLegend } from './chart-legend';
import { AXIS_TICK, CHART, ChartTooltip } from './chart-tooltip';
import type { HeadToHead } from './types';

/*
 * STAT-09 — both players' points per matchday on one axis.
 */
export function DuelChart({
    duel,
    aName,
    bName,
}: {
    duel: HeadToHead;
    aName: string;
    bName: string;
}) {
    const { t } = useTranslation();

    return (
        <div>
            <ChartLegend
                items={[
                    { label: aName, color: CHART.one },
                    { label: bName, color: CHART.two },
                ]}
            />
            <div className="h-60 px-1 pb-2">
                <ResponsiveContainer width="100%" height="100%">
                    <LineChart
                        data={duel.lines}
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
                            cursor={{ stroke: CHART.grid }}
                            content={({ active, label }) => {
                                const line = duel.lines.find(
                                    (each) => each.matchday === label,
                                );

                                return active && line ? (
                                    <ChartTooltip
                                        title={t('Matchday :number', {
                                            number: line.matchday,
                                        })}
                                        rows={[
                                            {
                                                label: aName,
                                                value: line.a,
                                                color: CHART.one,
                                            },
                                            {
                                                label: bName,
                                                value: line.b,
                                                color: CHART.two,
                                            },
                                        ]}
                                    />
                                ) : null;
                            }}
                        />
                        <Line
                            dataKey="a"
                            stroke={CHART.one}
                            strokeWidth={2}
                            dot={{ r: 4, fill: CHART.one, strokeWidth: 0 }}
                            isAnimationActive={false}
                        />
                        <Line
                            dataKey="b"
                            stroke={CHART.two}
                            strokeWidth={2}
                            dot={{ r: 4, fill: CHART.two, strokeWidth: 0 }}
                            isAnimationActive={false}
                        />
                    </LineChart>
                </ResponsiveContainer>
            </div>
        </div>
    );
}
