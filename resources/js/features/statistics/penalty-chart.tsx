import {
    Bar,
    BarChart,
    CartesianGrid,
    Line,
    LineChart,
    ReferenceLine,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';
import { useTranslation } from '@/hooks/use-translation';
import { formatCents } from '@/lib/money';
import { AXIS_TICK, CHART, ChartTooltip } from './chart-tooltip';
import type { MatchdayLine } from './types';

/*
 * STAT-06 — the penalty of each matchday, and the season's running total in
 * a graph of its own (one axis each); the interim settlement (PEN-04) is
 * marked in both.
 */
export function PenaltyChart({
    lines,
    settlementMatchday,
}: {
    lines: MatchdayLine[];
    settlementMatchday: number | null;
}) {
    const { t, locale } = useTranslation();
    const euros = (cents: number) => formatCents(cents, locale);
    const data = lines.map((line) => ({
        ...line,
        penalty: line.penaltyCents / 100,
        cumulative: line.cumulativePenaltyCents / 100,
    }));

    const tooltip = ({
        active,
        label,
    }: {
        active?: boolean;
        label?: string | number;
    }) => {
        const line = lines.find((each) => each.matchday === label);

        return active && line ? (
            <ChartTooltip
                title={t('Matchday :number', { number: line.matchday })}
                rows={[
                    {
                        label: t('Penalty'),
                        value: euros(line.penaltyCents),
                        color: CHART.one,
                    },
                    {
                        label: t('So far'),
                        value: euros(line.cumulativePenaltyCents),
                        color: CHART.two,
                    },
                ]}
            />
        ) : null;
    };

    const settlement =
        settlementMatchday === null ? null : (
            <ReferenceLine
                x={settlementMatchday}
                stroke={CHART.reference}
                strokeDasharray="4 3"
                label={{
                    value: t('Interim settlement'),
                    position: 'insideTopRight',
                    fill: CHART.axis,
                    fontSize: 11,
                }}
            />
        );

    const axes = (
        <>
            <CartesianGrid vertical={false} stroke={CHART.grid} />
            <XAxis
                dataKey="matchday"
                tick={AXIS_TICK}
                tickLine={false}
                axisLine={{ stroke: CHART.grid }}
            />
            <YAxis
                width={44}
                tick={AXIS_TICK}
                tickLine={false}
                axisLine={false}
                tickFormatter={(value: number) => `${value} €`}
            />
        </>
    );

    return (
        <div className="grid gap-2 px-1 pt-3 pb-2">
            <p className="px-3 text-xs font-medium text-brand-muted">
                {t('Penalty per matchday')}
            </p>
            <div className="h-44">
                <ResponsiveContainer width="100%" height="100%">
                    <BarChart
                        data={data}
                        margin={{ top: 12, right: 12, bottom: 0, left: 0 }}
                    >
                        {axes}
                        <Tooltip
                            cursor={{ fill: CHART.grid }}
                            content={tooltip}
                        />
                        {settlement}
                        <Bar
                            dataKey="penalty"
                            fill={CHART.one}
                            radius={[4, 4, 0, 0]}
                            maxBarSize={24}
                            isAnimationActive={false}
                        />
                    </BarChart>
                </ResponsiveContainer>
            </div>
            <p className="px-3 text-xs font-medium text-brand-muted">
                {t('Penalties so far this season')}
            </p>
            <div className="h-44">
                <ResponsiveContainer width="100%" height="100%">
                    <LineChart
                        data={data}
                        margin={{ top: 12, right: 12, bottom: 0, left: 0 }}
                    >
                        {axes}
                        <Tooltip
                            cursor={{ stroke: CHART.grid }}
                            content={tooltip}
                        />
                        {settlement}
                        <Line
                            dataKey="cumulative"
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
