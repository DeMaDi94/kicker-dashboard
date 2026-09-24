<?php

declare(strict_types=1);

namespace App\Domain\Statistics;

/**
 * A player's statistics for one season (STAT-03–07), over its complete
 * matchdays only (STAT-02).
 */
final readonly class PlayerSeasonStats
{
    /**
     * @param  list<int>  $bestMatchdays
     * @param  list<int>  $worstMatchdays
     * @param  list<MatchdayLine>  $lines
     * @param  list<array{matchday: int, place: int, grade: FormGrade}>  $form
     */
    public function __construct(
        public int $matchdaysPlayed,
        public int $totalPoints,
        public ?float $averagePoints,
        public ?int $bestPoints,
        public array $bestMatchdays,
        public ?int $worstPoints,
        public array $worstMatchdays,
        public int $place,
        public int $penaltyCents,
        public ?int $firstHalfPenaltyCents,
        public ?int $secondHalfPenaltyCents,
        public array $lines,
        public int $wins,
        public int $lanterns,
        public int $penaltyFreeMatchdays,
        public int $longestPenaltyFreeStreak,
        public array $form,
    ) {}

    /** STAT-07 — the day's places of the last five complete matchdays. */
    private const int FORM_LENGTH = 5;

    public static function of(int $playerId, SeasonTimeline $season): self
    {
        $lines = [];
        $cumulative = 0;
        $wins = 0;
        $lanterns = 0;
        $penaltyFree = 0;
        $streak = 0;
        $longest = 0;
        $form = [];

        foreach ($season->matchdays as $matchday) {
            $penalty = $matchday->penalties[$playerId] ?? 0;
            $cumulative += $penalty;
            $place = $matchday->places[$playerId] ?? 0;

            $lines[] = new MatchdayLine(
                $matchday->number,
                $matchday->points[$playerId] ?? 0,
                round($matchday->average(), 1),
                $place,
                $matchday->overallPlaces[$playerId] ?? 0,
                $penalty,
                $cumulative,
            );

            $wins += in_array($playerId, $matchday->winners(), true) ? 1 : 0;
            $lanterns += in_array($playerId, $matchday->lanterns(), true) ? 1 : 0;

            if ($penalty === 0) {
                $penaltyFree++;
                $longest = max($longest, ++$streak);
            } else {
                $streak = 0;
            }

            $form[] = ['matchday' => $matchday->number, 'place' => $place, 'grade' => FormGrade::of($place, $matchday->lastPlace())];
        }

        $points = array_map(fn (MatchdayLine $line): int => $line->points, $lines);
        $best = $points === [] ? null : max($points);
        $worst = $points === [] ? null : min($points);
        $standing = $season->standingOf($playerId);

        return new self(
            matchdaysPlayed: count($lines),
            totalPoints: array_sum($points),
            averagePoints: $points === [] ? null : round(array_sum($points) / count($points), 1),
            bestPoints: $best,
            bestMatchdays: self::matchdaysWith($lines, $best),
            worstPoints: $worst,
            worstMatchdays: self::matchdaysWith($lines, $worst),
            place: $standing->place ?? 0,
            penaltyCents: $standing->penaltyCents ?? 0,
            firstHalfPenaltyCents: $standing?->firstHalfPenaltyCents,
            secondHalfPenaltyCents: $standing?->secondHalfPenaltyCents,
            lines: $lines,
            wins: $wins,
            lanterns: $lanterns,
            penaltyFreeMatchdays: $penaltyFree,
            longestPenaltyFreeStreak: $longest,
            form: array_slice($form, -self::FORM_LENGTH),
        );
    }

    /**
     * @param  list<MatchdayLine>  $lines
     * @return list<int>
     */
    private static function matchdaysWith(array $lines, ?int $points): array
    {
        return array_values(array_map(
            fn (MatchdayLine $line): int => $line->matchday,
            array_filter($lines, fn (MatchdayLine $line): bool => $line->points === $points),
        ));
    }
}
