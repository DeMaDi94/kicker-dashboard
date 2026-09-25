<?php

declare(strict_types=1);

use App\Domain\Visits\Bots;
use App\Domain\Visits\PublicPage;
use App\Domain\Visits\VisitorMark;
use App\Domain\Visits\VisitPeriod;
use App\Domain\Visits\VisitRetention;
use App\Domain\Visits\VisitRow;
use App\Domain\Visits\VisitStatistics;

const BROWSER = 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.0 Mobile/15E148 Safari/604.1';

function berlin(string $time): DateTimeImmutable
{
    return new DateTimeImmutable($time, new DateTimeZone('Europe/Berlin'));
}

describe('VIS-01 · bots and crawlers do not count (D14)', function () {
    it('counts a browser', function () {
        expect(Bots::matches(BROWSER))->toBeFalse();
    });

    it('does not count a request without a user agent', function (?string $agent) {
        expect(Bots::matches($agent))->toBeTrue();
    })->with([null, '', '   ']);

    it('does not count an agent carrying one of the words, in any case', function (string $agent) {
        expect(Bots::matches($agent))->toBeTrue();
    })->with([
        'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
        'Mozilla/5.0 (compatible; bingbot/2.0)',
        'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; ClaudeBot/1.0)',
        'Screaming Frog SEO Spider/19.0',
        'Mozilla/5.0 (compatible; Yahoo! Slurp)',
        'facebookexternalhit/1.1',
        'WhatsApp/2.23 link Preview',
        'UptimeRobot-Monitor/2.0',
        'Mozilla/5.0 HeadlessChrome/120.0',
        'curl/8.4.0',
        'Wget/1.21',
        'python-requests/2.31',
        'Go-http-client/2.0',
        'CCBot CRAWLER',
    ]);
});

describe('VIS-02 · the visitor mark of the day (D14)', function () {
    it('is 16 hex digits of SHA-256 over the salt, the IP and the user agent', function () {
        $mark = VisitorMark::of('salt', '203.0.113.7', BROWSER);

        expect($mark)->toMatch('/^[0-9a-f]{16}$/')
            ->and(str_starts_with(hash('sha256', "salt\n203.0.113.7\n".BROWSER), $mark))->toBeTrue()
            ->and($mark)->not->toContain('203.0.113.7');
    });

    it('tells visitors of one day apart and stays the same for one visitor', function () {
        expect(VisitorMark::of('monday', '203.0.113.7', BROWSER))->toBe(VisitorMark::of('monday', '203.0.113.7', BROWSER))
            ->and(VisitorMark::of('monday', '203.0.113.8', BROWSER))->not->toBe(VisitorMark::of('monday', '203.0.113.7', BROWSER))
            ->and(VisitorMark::of('monday', '203.0.113.7', 'Firefox'))->not->toBe(VisitorMark::of('monday', '203.0.113.7', BROWSER));
    });

    it('changes with the day’s salt', function () {
        expect(VisitorMark::of('tuesday', '203.0.113.7', BROWSER))->not->toBe(VisitorMark::of('monday', '203.0.113.7', BROWSER));
    });
});

describe('VIS-03 · visits older than 12 months are deleted', function () {
    it('keeps 12 months back from now', function () {
        expect(VisitRetention::cutoff(berlin('2026-09-25 03:00'))->format('Y-m-d H:i'))->toBe('2025-09-25 03:00');
    });
});

describe('VIS-04 · the period', function () {
    it('offers the last 7, 30, 90 or 365 days', function () {
        expect(array_map(fn (VisitPeriod $period): int => $period->value, VisitPeriod::cases()))->toBe([7, 30, 90, 365]);
    });

    it('preselects 30 days, and opens them for any other value (D14)', function (?int $days) {
        expect(VisitPeriod::fromDays($days))->toBe(VisitPeriod::Month);
    })->with([null, 0, 12, 31, -7]);

    it('takes a period it offers', function () {
        expect(VisitPeriod::fromDays(7))->toBe(VisitPeriod::Week)
            ->and(VisitPeriod::fromDays(365))->toBe(VisitPeriod::Year);
    });

    it('includes today', function () {
        expect(VisitPeriod::Week->firstDay(berlin('2026-09-25 18:30'))->format('Y-m-d H:i'))->toBe('2026-09-19 00:00');
    });
});

/*
 * Worked by hand, a week up to Friday 2026-09-25 (Mon 21 … Sun 27 in the grid):
 *   Sat 19  none
 *   Mon 21  a season 09:15, a records 09:40, b season 20:05     3 visits, 2 visitors
 *   Fri 25  a season 09:10 (a new mark: a new day), c player 23:59   2 visits, 2 visitors
 *   Thu 18  x season — before the period, ignored
 */
function weekOfVisits(): VisitStatistics
{
    return VisitStatistics::of(VisitPeriod::Week, berlin('2026-09-25 23:59:59'), [
        new VisitRow(PublicPage::SeasonView, berlin('2026-09-18 12:00'), 'x'),
        new VisitRow(PublicPage::SeasonView, berlin('2026-09-21 09:15'), 'a-mon'),
        new VisitRow(PublicPage::Records, berlin('2026-09-21 09:40'), 'a-mon'),
        new VisitRow(PublicPage::SeasonView, berlin('2026-09-21 20:05'), 'b-mon'),
        new VisitRow(PublicPage::SeasonView, berlin('2026-09-25 09:10'), 'a-fri'),
        new VisitRow(PublicPage::Player, berlin('2026-09-25 23:59'), 'c-fri'),
    ]);
}

describe('VIS-05 · visits and visitors of the period', function () {
    it('sums the visits, and the visitors as the sum of the days', function () {
        $week = weekOfVisits();

        expect($week->visits)->toBe(5)
            ->and($week->visitors)->toBe(4);
    });

    it('lists every day of the period, oldest first, a quiet one with 0', function () {
        expect(weekOfVisits()->days)->toBe([
            ['date' => '2026-09-19', 'visits' => 0, 'visitors' => 0],
            ['date' => '2026-09-20', 'visits' => 0, 'visitors' => 0],
            ['date' => '2026-09-21', 'visits' => 3, 'visitors' => 2],
            ['date' => '2026-09-22', 'visits' => 0, 'visitors' => 0],
            ['date' => '2026-09-23', 'visits' => 0, 'visitors' => 0],
            ['date' => '2026-09-24', 'visits' => 0, 'visitors' => 0],
            ['date' => '2026-09-25', 'visits' => 2, 'visitors' => 2],
        ]);
    });

    it('counts one visitor twice on two days', function () {
        $days = VisitStatistics::of(VisitPeriod::Week, berlin('2026-09-25 12:00'), [
            new VisitRow(PublicPage::SeasonView, berlin('2026-09-24 12:00'), 'same'),
            new VisitRow(PublicPage::SeasonView, berlin('2026-09-25 12:00'), 'same'),
        ]);

        expect($days->visitors)->toBe(2);
    });
});

describe('VIS-06 · visits by weekday and hour, and per page', function () {
    it('fills the heatmap Monday to Sunday, hour 0 to 23', function () {
        $heatmap = weekOfVisits()->heatmap;

        expect($heatmap)->toHaveCount(7)
            ->and($heatmap[0])->toHaveCount(24)
            ->and($heatmap[0][9])->toBe(2)
            ->and($heatmap[0][20])->toBe(1)
            ->and($heatmap[4][9])->toBe(1)
            ->and($heatmap[4][23])->toBe(1)
            ->and(array_sum(array_map('array_sum', $heatmap)))->toBe(5);
    });

    it('sums the hours over the weekdays', function () {
        $hours = weekOfVisits()->hours;

        expect($hours)->toHaveCount(24)
            ->and($hours[9])->toBe(3)
            ->and($hours[20])->toBe(1)
            ->and($hours[23])->toBe(1)
            ->and(array_sum($hours))->toBe(5);
    });

    it('lists every page, the most visited first, a tie in the order of VIS-01 (D14)', function () {
        expect(weekOfVisits()->pages)->toBe([
            ['page' => PublicPage::SeasonView, 'visits' => 3],
            ['page' => PublicPage::Player, 'visits' => 1],
            ['page' => PublicPage::Records, 'visits' => 1],
            ['page' => PublicPage::HeadToHead, 'visits' => 0],
        ]);
    });
});
