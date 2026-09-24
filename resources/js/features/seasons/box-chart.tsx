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
import { AXIS_TICK, CHART, ChartTooltip } from '@/components/core/chart';
import { useTranslation } from '@/hooks/use-translation';
import { formatCents } from '@/lib/money';
import type { PenaltyBoxMatchday } from './types';

/*
 * STAT-14 — the money that went into the box on each matchday, and the box's
 * balance over the season in a graph of its own (one axis each, as STAT-06);
 * the interim settlement (PEN-04) is marked in both.
 */
export function BoxChart({
    matchdays,
    settlementMatchday,
}: {
    matchdays: PenaltyBoxMatchday[];
    settlementMatchday: number | null;
}) {
    const { t, locale } = useTranslation();
    const euros = (cents: number) => formatCents(cents, locale);
    const data = matchdays.map((each) => ({
        matchday: each.matchday,
        money: each.cents / 100,
        balance: each.cumulativeCents / 100,
    }));

    const tooltip = ({
        active,
        label,
    }: {
        active?: boolean;
        label?: string | number;
    }) => {
        const day = matchdays.find((each) => each.matchday === label);

        return active && day ? (
            <ChartTooltip
                title={t('Matchday :number', { number: day.matchday })}
                rows={[
                    {
                        label: t('Into the box'),
                        value: euros(day.cents),
                        color: CHART.one,
                    },
                    {
                        label: t('Box balance'),
                        value: euros(day.cumulativeCents),
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
        <div className="grid gap-2">
            <p className="text-xs font-medium text-brand-muted">
                {t('Money per matchday')}
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
                            dataKey="money"
                            fill={CHART.one}
                            radius={[4, 4, 0, 0]}
                            maxBarSize={24}
                            isAnimationActive={false}
                        />
                    </BarChart>
                </ResponsiveContainer>
            </div>
            <p className="text-xs font-medium text-brand-muted">
                {t('Box balance over the season')}
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
                            dataKey="balance"
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
