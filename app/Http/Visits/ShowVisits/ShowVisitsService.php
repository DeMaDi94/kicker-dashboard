<?php

declare(strict_types=1);

namespace App\Http\Visits\ShowVisits;

use App\Domain\Visits\VisitPeriod;
use App\Domain\Visits\VisitRow;
use App\Domain\Visits\VisitStatistics;
use App\Models\Visit;

/**
 * VIS-04 – VIS-06 — the visit statistics of the period chosen, up to and
 * including today, in the app's time zone (Europe/Berlin).
 *
 * @phpstan-type VisitsPage array{
 *     days: int,
 *     periods: list<int>,
 *     statistics: VisitStatistics
 * }
 */
final class ShowVisitsService
{
    /**
     * @return VisitsPage
     */
    public function __invoke(VisitPeriod $period): array
    {
        $today = now()->toDateTimeImmutable();

        $rows = Visit::query()
            ->where('visited_at', '>=', $period->firstDay($today))
            ->lazyById(1000)
            ->map(fn (Visit $visit): VisitRow => new VisitRow($visit->page, $visit->visited_at->toDateTimeImmutable(), $visit->visitor));

        return [
            'days' => $period->value,
            'periods' => array_map(fn (VisitPeriod $each): int => $each->value, VisitPeriod::cases()),
            'statistics' => VisitStatistics::of($period, $today, $rows),
        ];
    }
}
