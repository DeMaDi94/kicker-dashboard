<?php

declare(strict_types=1);

namespace App\Domain\Visits;

use DateTimeImmutable;

/**
 * VIS-05, VIS-06 — what the admins see for a period: the visits and the
 * visitors in sum and per day, the visits by weekday and hour, and per page.
 * Visitors count per day, and the period's sum is the sum of the days (VIS-05)
 * — a mark changes daily (VIS-02), so no visitor can be followed across days.
 */
final readonly class VisitStatistics
{
    /**
     * @param  list<array{date: string, visits: int, visitors: int}>  $days  every day of the period, oldest first
     * @param  list<list<int>>  $heatmap  Monday to Sunday, each hour 0 to 23
     * @param  list<int>  $hours  hour 0 to 23
     * @param  list<array{page: PublicPage, visits: int}>  $pages  the most visited first
     */
    public function __construct(
        public int $visits,
        public int $visitors,
        public array $days,
        public array $heatmap,
        public array $hours,
        public array $pages,
    ) {}

    /**
     * @param  iterable<VisitRow>  $rows
     */
    public static function of(VisitPeriod $period, DateTimeImmutable $today, iterable $rows): self
    {
        $day = $period->firstDay($today);
        $visits = $visitors = [];

        for ($i = 0; $i < $period->value; $i++) {
            $visits[$day->format('Y-m-d')] = 0;
            $visitors[$day->format('Y-m-d')] = [];
            $day = $day->modify('+1 day');
        }

        $heatmap = array_fill(0, 7, array_fill(0, 24, 0));
        $pages = array_fill_keys(array_map(fn (PublicPage $page): string => $page->value, PublicPage::cases()), 0);

        foreach ($rows as $row) {
            $date = $row->at->format('Y-m-d');

            if (! isset($visits[$date])) {
                continue;
            }

            $visits[$date]++;
            $visitors[$date][$row->visitor] = true;
            $heatmap[(int) $row->at->format('N') - 1][(int) $row->at->format('G')]++;
            $pages[$row->page->value]++;
        }

        $days = [];

        foreach ($visits as $date => $count) {
            $days[] = ['date' => (string) $date, 'visits' => $count, 'visitors' => count($visitors[$date])];
        }

        $hours = array_map(fn (int $hour): int => array_sum(array_column($heatmap, $hour)), range(0, 23));

        // D14 — every public page, one nobody called with 0; a tie keeps the order of VIS-01.
        $byPage = array_map(fn (PublicPage $page): array => ['page' => $page, 'visits' => $pages[$page->value]], PublicPage::cases());
        usort($byPage, fn (array $a, array $b): int => $b['visits'] <=> $a['visits']);

        return new self(
            array_sum($visits),
            array_sum(array_column($days, 'visitors')),
            $days,
            $heatmap,
            $hours,
            $byPage,
        );
    }
}
