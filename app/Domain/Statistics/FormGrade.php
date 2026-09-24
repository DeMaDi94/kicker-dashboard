<?php

declare(strict_types=1);

namespace App\Domain\Statistics;

/**
 * STAT-07 — a day's place judged by the third of that day's places it falls
 * in. The thirds round up (D11): of 13 places, 1–5 are good, 9–13 bad.
 */
enum FormGrade: string
{
    case Good = 'good';
    case Middle = 'middle';
    case Bad = 'bad';

    public static function of(int $place, int $lastPlace): self
    {
        $third = (int) ceil($lastPlace / 3);

        return match (true) {
            $place <= $third => self::Good,
            $place > $lastPlace - $third => self::Bad,
            default => self::Middle,
        };
    }
}
