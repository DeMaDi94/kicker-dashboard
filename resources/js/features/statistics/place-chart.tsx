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
import { AXIS_TICK, CHART, ChartTooltip } from '@/components/core/chart';
import type { MatchdayLine } from './types';

/*
 * STAT-05 — the place in the overall table after each matchday; first place
 * at the top.
 */
export function PlaceChart({
    lines,
    players,
}: {
    lines: MatchdayLine[];
    players: number;
}) {
    const { t } = useTranslation();

    return (
        <div className="h-56 px-1 pt-3 pb-2">
            <ResponsiveContainer width="100%" height="100%">
                <LineChart
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
                        reversed
                        width={36}
                        domain={[1, Math.max(players, 1)]}
                        allowDecimals={false}
                        tick={AXIS_TICK}
                        tickLine={false}
                        axisLine={false}
                    />
                    <Tooltip
                        cursor={{ stroke: CHART.grid }}
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
                                            label: t('Place'),
                                            value: `${line.overallPlace}.`,
                                            color: CHART.one,
                                        },
                                    ]}
                                />
                            ) : null;
                        }}
                    />
                    <Line
                        dataKey="overallPlace"
                        stroke={CHART.one}
                        strokeWidth={2}
                        dot={{ r: 4, fill: CHART.one, strokeWidth: 0 }}
                        activeDot={{ r: 5 }}
                        isAnimationActive={false}
                    />
                </LineChart>
            </ResponsiveContainer>
        </div>
    );
}
