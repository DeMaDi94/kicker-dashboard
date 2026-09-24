<?php

declare(strict_types=1);

namespace App\Domain\Statistics;

/**
 * STAT-10 / STAT-15 — the league's records over the seasons handed in: all of them, or
 * the one chosen. A tie lists every holder, in the order the seasons are
 * handed in, then by matchday, then by the overall table (D11). Null where no
 * complete matchday exists yet.
 */
final readonly class LeagueRecords
{
    public function __construct(
        public ?LeagueRecord $highestScore,
        public ?LeagueRecord $lowestScore,
        public ?LeagueRecord $mostWins,
        public ?LeagueRecord $highestPenalty,
        public ?LeagueRecord $closestMatchday,
        public ?LeagueRecord $mostExpensiveMatchday,
    ) {}

    /**
     * @param  array<int, array{name: string, timeline: SeasonTimeline}>  $seasons  season id => season
     */
    public static function of(array $seasons): self
    {
        $high = $low = $wins = $penalty = $close = $expensive = [];

        foreach ($seasons as $seasonId => $season) {
            $timeline = $season['timeline'];
            $names = $timeline->participants;
            $winCount = [];

            $order = $timeline->tableOrder();
            $player = fn (int $id, ?int $matchday = null): RecordHolder => new RecordHolder(
                $seasonId, $season['name'], $id, $names[$id] ?? '', $matchday, $timeline->aliasOf($id),
            );

            foreach ($timeline->matchdays as $matchday) {
                foreach ($order as $playerId) {
                    if (! isset($matchday->points[$playerId])) {
                        continue;
                    }

                    $holder = $player($playerId, $matchday->number);
                    $high[] = [$matchday->points[$playerId], $holder];
                    $low[] = [$matchday->points[$playerId], $holder];
                }

                foreach ($matchday->winners() as $playerId) {
                    $winCount[$playerId] = ($winCount[$playerId] ?? 0) + 1;
                }

                $close[] = [$matchday->spread(), new RecordHolder($seasonId, $season['name'], matchday: $matchday->number)];
                // STAT-15 — the matchday on which the most money went into the box.
                $expensive[] = [$matchday->penaltyTotal(), new RecordHolder($seasonId, $season['name'], matchday: $matchday->number)];
            }

            foreach ($order as $playerId) {
                if (isset($winCount[$playerId])) {
                    $wins[] = [$winCount[$playerId], $player($playerId)];
                }
            }

            if ($timeline->matchdays !== []) {
                foreach ($timeline->standings as $row) {
                    $penalty[] = [$row->penaltyCents, $player($row->playerId)];
                }
            }
        }

        return new self(
            self::top($high, max: true),
            self::top($low, max: false),
            self::top($wins, max: true),
            self::top($penalty, max: true),
            self::top($close, max: false),
            self::top($expensive, max: true),
        );
    }

    /**
     * @param  list<array{int, RecordHolder}>  $candidates
     */
    private static function top(array $candidates, bool $max): ?LeagueRecord
    {
        if ($candidates === []) {
            return null;
        }

        $values = array_map(fn (array $candidate): int => $candidate[0], $candidates);
        $value = $max ? max($values) : min($values);

        return new LeagueRecord($value, array_values(array_map(
            fn (array $candidate): RecordHolder => $candidate[1],
            array_filter($candidates, fn (array $candidate): bool => $candidate[0] === $value),
        )));
    }
}
