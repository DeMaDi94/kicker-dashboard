<?php

declare(strict_types=1);

use App\Domain\Visits\PublicPage;
use App\Models\Visit;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia;

function storedVisit(PublicPage $page, string $at, string $visitor): void
{
    Visit::create(['page' => $page, 'visitor' => str_pad($visitor, 16, '0'), 'visited_at' => $at]);
}

beforeEach(function () {
    Carbon::setTestNow('2026-09-25 18:00:00');
});

describe('VIS-04 · only admins see the visit statistics', function () {
    it('sends a guest to the sign-in and refuses a plain user', function () {
        $this->get(route('visits.index'))->assertRedirect(route('login'));
        $this->actingAs(member())->get(route('visits.index'))->assertForbidden();
    });

    it('opens for an admin, the navigation entry gated by visits.view', function () {
        $this->actingAs(admin())->get(route('visits.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('visits/index')
                ->where('auth.permissions', fn ($permissions) => collect($permissions)->contains('visits.view')));

        $this->actingAs(member())->get(route('home'))
            ->assertInertia(fn (AssertableInertia $page) => $page->where('auth.permissions', []));
    });

    it('offers the last 7, 30, 90 or 365 days and preselects 30', function () {
        $this->actingAs(admin())->get(route('visits.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('periods', [7, 30, 90, 365])
                ->where('days', 30)
                ->has('statistics.days', 30)
                ->where('statistics.days.0.date', '2026-08-27')
                ->where('statistics.days.29.date', '2026-09-25'));
    });

    it('shows the period chosen, today included', function () {
        $this->actingAs(admin())->get(route('visits.index', ['days' => 7]))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('days', 7)
                ->has('statistics.days', 7)
                ->where('statistics.days.0.date', '2026-09-19')
                ->where('statistics.days.6.date', '2026-09-25'));
    });

    it('opens the 30 days for a value it does not offer (D14)', function (string $days) {
        $this->actingAs(admin())->get(route('visits.index', ['days' => $days]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('days', 30)->has('statistics.days', 30));
    })->with(['12', 'abc', '0']);

    it('counts in German time', function () {
        // 23:30 in Berlin on the 24th is a visit of the 24th, at 23 o’clock.
        storedVisit(PublicPage::Records, '2026-09-24 23:30:00', 'a');

        $this->actingAs(admin())->get(route('visits.index', ['days' => 7]))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('statistics.days.5', ['date' => '2026-09-24', 'visits' => 1, 'visitors' => 1])
                ->where('statistics.hours.23', 1));
    });
});

describe('VIS-05 · visits and visitors of the period', function () {
    it('sums the visits and the visitors of each day', function () {
        storedVisit(PublicPage::SeasonView, '2026-09-24 09:00:00', 'a');
        storedVisit(PublicPage::Records, '2026-09-24 09:05:00', 'a');
        storedVisit(PublicPage::SeasonView, '2026-09-25 10:00:00', 'b');
        storedVisit(PublicPage::SeasonView, '2026-09-25 11:00:00', 'c');
        storedVisit(PublicPage::SeasonView, '2026-09-18 11:00:00', 'z');

        $this->actingAs(admin())->get(route('visits.index', ['days' => 7]))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('statistics.visits', 4)
                ->where('statistics.visitors', 3)
                ->where('statistics.days.5', ['date' => '2026-09-24', 'visits' => 2, 'visitors' => 1])
                ->where('statistics.days.6', ['date' => '2026-09-25', 'visits' => 2, 'visitors' => 2]));
    });
});

describe('VIS-06 · visits by weekday and hour, and per page', function () {
    it('sends the heatmap, the hours and the pages, the most visited first', function () {
        // Thursday 2026-09-24 and Friday 2026-09-25.
        storedVisit(PublicPage::Player, '2026-09-24 09:00:00', 'a');
        storedVisit(PublicPage::Player, '2026-09-25 09:30:00', 'b');
        storedVisit(PublicPage::Records, '2026-09-25 17:00:00', 'c');

        $this->actingAs(admin())->get(route('visits.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('statistics.heatmap', 7)
                ->has('statistics.heatmap.0', 24)
                ->where('statistics.heatmap.3.9', 1)
                ->where('statistics.heatmap.4.9', 1)
                ->where('statistics.heatmap.4.17', 1)
                ->has('statistics.hours', 24)
                ->where('statistics.hours.9', 2)
                ->where('statistics.pages', [
                    ['page' => 'player', 'visits' => 2],
                    ['page' => 'records', 'visits' => 1],
                    ['page' => 'season', 'visits' => 0],
                    ['page' => 'compare', 'visits' => 0],
                ]));
    });
});
