<?php

declare(strict_types=1);

use App\Domain\Visits\PublicPage;
use App\Models\Player;
use App\Models\Season;
use App\Models\Visit;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

const A_BROWSER = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_0) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15';

/**
 * A season of Anna and Bert, so every public page renders.
 *
 * @return array{Season, Player}
 */
function visitedLeague(): array
{
    $season = Season::factory()->create(['name' => '2026/27']);
    $anna = Player::firstOrCreate(['name' => 'Anna'], ['alias' => 'anna']);
    $season->players()->attach([$anna->id, Player::firstOrCreate(['name' => 'Bert'], ['alias' => 'bert'])->id]);

    return [$season, $anna];
}

beforeEach(function () {
    $this->withHeader('User-Agent', A_BROWSER);
});

describe('VIS-01 · a guest’s visit of a public page is counted', function () {
    it('counts each public page under its name', function () {
        [$season, $anna] = visitedLeague();

        $this->get(route('home'))->assertOk();
        $this->get(route('seasons.show', $season))->assertOk();
        $this->get(route('players.show', $anna))->assertOk();
        $this->get(route('seasons.compare', $season))->assertOk();
        $this->get(route('records'))->assertOk();

        expect(Visit::orderBy('id')->pluck('page')->all())->toBe([
            PublicPage::SeasonView, PublicPage::SeasonView, PublicPage::Player, PublicPage::HeadToHead, PublicPage::Records,
        ]);
    });

    it('counts an Inertia visit from one page to the next', function () {
        visitedLeague();

        $this->withHeader('X-Inertia', 'true')->get(route('records'))->assertOk();

        expect(Visit::count())->toBe(1);
    });

    it('does not count a signed-in user, admin or not', function () {
        visitedLeague();

        $this->actingAs(member())->get(route('records'))->assertOk();
        $this->actingAs(admin())->get(route('home'))->assertOk();

        expect(Visit::count())->toBe(0);
    });

    it('does not count a bot or a request without a user agent (D14)', function (string $agent) {
        visitedLeague();

        $this->withHeader('User-Agent', $agent)->get(route('records'))->assertOk();

        expect(Visit::count())->toBe(0);
    })->with(['Mozilla/5.0 (compatible; Googlebot/2.1)', 'curl/8.4.0', '']);

    it('does not count an Inertia prefetch or partial reload (D14)', function () {
        visitedLeague();

        $this->withHeaders(['X-Inertia' => 'true', 'Purpose' => 'prefetch'])->get(route('records'))->assertOk();
        $this->withHeaders(['X-Inertia' => 'true', 'X-Inertia-Partial-Component' => 'statistics/records', 'X-Inertia-Partial-Data' => 'records'])
            ->get(route('records'))->assertOk();

        expect(Visit::count())->toBe(0);
    });

    it('does not count a page that is not found (D14)', function () {
        $this->get(route('seasons.show', 999))->assertNotFound();
        $this->get(route('players.show', 999))->assertNotFound();

        expect(Visit::count())->toBe(0);
    });

    it('does not count a screen that is not public', function () {
        visitedLeague();

        $this->get(route('login'))->assertOk();
        $this->actingAs(admin())->get(route('visits.index'))->assertOk();

        expect(Visit::count())->toBe(0);
    });
});

describe('VIS-02 · only the page, the time and the day’s mark are stored', function () {
    it('stores no IP address and no user agent', function () {
        visitedLeague();
        Carbon::setTestNow('2026-09-25 14:30:00');

        $this->get(route('records'), ['REMOTE_ADDR' => '203.0.113.7']);

        expect(Schema::getColumnListing('visits'))->toEqualCanonicalizing(['id', 'page', 'visitor', 'visited_at']);

        $visit = Visit::sole();

        expect($visit->visited_at->format('Y-m-d H:i:s'))->toBe('2026-09-25 14:30:00')
            ->and($visit->visitor)->toMatch('/^[0-9a-f]{16}$/')
            ->and(json_encode($visit->getAttributes()))->not->toContain('203.0.113.7')
            ->and(json_encode($visit->getAttributes()))->not->toContain('Safari');
    });

    it('sets no cookie of its own', function () {
        visitedLeague();

        $counted = $this->get(route('records'))->headers->getCookies();
        $notCounted = $this->withHeader('User-Agent', 'curl/8.4.0')->get(route('records'))->headers->getCookies();

        expect(Visit::count())->toBe(1)
            ->and(array_map(fn ($cookie) => $cookie->getName(), $counted))
            ->toEqualCanonicalizing(array_map(fn ($cookie) => $cookie->getName(), $notCounted));
    });

    it('tells visitors of one day apart and gives each a new mark the next day', function () {
        visitedLeague();
        Carbon::setTestNow('2026-09-25 10:00:00');

        $this->get(route('records'), ['REMOTE_ADDR' => '203.0.113.7']);
        $this->get(route('home'), ['REMOTE_ADDR' => '203.0.113.7']);
        $this->get(route('home'), ['REMOTE_ADDR' => '203.0.113.8']);

        Carbon::setTestNow('2026-09-26 10:00:00');
        $this->get(route('home'), ['REMOTE_ADDR' => '203.0.113.7']);

        [$first, $again, $other, $nextDay] = Visit::orderBy('id')->pluck('visitor')->all();

        expect($again)->toBe($first)
            ->and($other)->not->toBe($first)
            ->and($nextDay)->not->toBe($first);
    });
});

describe('VIS-03 · visits older than 12 months are deleted', function () {
    it('prunes them with model:prune, nightly', function () {
        Carbon::setTestNow('2026-09-25 00:00:00');

        foreach (['2025-09-24 23:59:59', '2025-09-25 00:00:00', '2026-09-24 12:00:00'] as $at) {
            Visit::create(['page' => PublicPage::Records, 'visitor' => str_repeat('a', 16), 'visited_at' => $at]);
        }

        $this->artisan('model:prune', ['--model' => Visit::class])->assertSuccessful();

        expect(Visit::orderBy('visited_at')->pluck('visited_at')->map->format('Y-m-d H:i:s')->all())
            ->toBe(['2025-09-25 00:00:00', '2026-09-24 12:00:00']);
    });

    it('schedules the pruning every day', function () {
        $this->artisan('schedule:list')->expectsOutputToContain('0 0 * * *  php artisan model:prune')->assertSuccessful();
    });
});
