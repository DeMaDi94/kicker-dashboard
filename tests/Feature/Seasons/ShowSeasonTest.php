<?php

declare(strict_types=1);

use App\Models\Player;
use App\Models\Score;
use App\Models\Season;
use Inertia\Testing\AssertableInertia;

/**
 * @param  array<string, int>  $points  player name => points
 */
function enterPoints(Season $season, int $matchday, array $points): void
{
    foreach ($points as $name => $value) {
        Score::create([
            'season_id' => $season->id,
            'player_id' => Player::where('name', $name)->sole()->id,
            'matchday' => $matchday,
            'points' => $value,
        ]);
    }
}

function leagueSeason(string $name = '2026/27'): Season
{
    $season = Season::factory()->create(['name' => $name, 'penalty_start_cents' => 450, 'penalty_step_cents' => 50]);

    foreach (['Anna', 'Bert', 'Cleo'] as $player) {
        $season->players()->attach(Player::firstOrCreate(['name' => $player], ['alias' => strtolower($player)]));
    }

    return $season;
}

describe('ACC-01 · the season view without signing in', function () {
    it('shows the season created last at /', function () {
        leagueSeason('2025/26');
        $latest = leagueSeason('2026/27');

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('seasons/show')
                ->where('season.id', $latest->id)
                ->where('seasons.0.name', '2026/27')
                ->where('seasons.1.name', '2025/26'));
    });

    it('shows an earlier season by its address', function () {
        $earlier = leagueSeason('2025/26');
        leagueSeason('2026/27');

        $this->get(route('seasons.show', $earlier))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('season.name', '2025/26'));
    });

    it('shows an empty view while there is no season', function () {
        $this->get(route('home'))
            ->assertInertia(fn (AssertableInertia $page) => $page->where('season', null)->where('standings', []));
    });

    it('shows the overall table with points and penalty sums (STD-01, PEN-03)', function () {
        $season = leagueSeason();
        enterPoints($season, 1, ['Anna' => 40, 'Bert' => 60, 'Cleo' => 60]);

        $this->get(route('home'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('standings.0.name', 'Bert')->where('standings.0.place', 1)->where('standings.0.points', 60)->where('standings.0.penaltyCents', 400)
                ->where('standings.1.name', 'Cleo')->where('standings.1.place', 1)
                ->where('standings.2.name', 'Anna')->where('standings.2.place', 2)->where('standings.2.penaltyCents', 450)
                ->where('standings.2.alias', 'anna'));
    });

    it('shows every matchday with points, places and penalties (MD-03, PEN-01)', function () {
        $season = leagueSeason();
        enterPoints($season, 2, ['Anna' => 40, 'Bert' => 60, 'Cleo' => 55]);

        $this->get(route('home'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('matchdays', 34)
                ->where('matchdays.1.number', 2)
                ->where('matchdays.1.complete', true)
                ->where('matchdays.1.rows.0.name', 'Bert')
                ->where('matchdays.1.rows.0.place', 1)
                ->where('matchdays.1.rows.0.penaltyCents', 350)
                ->where('matchdays.1.rows.2.name', 'Anna')
                ->where('matchdays.1.rows.2.penaltyCents', 450)
                ->where('matchdays.0.hasPoints', false));
    });
});

// MD-02 — no places or penalties, and nothing in the table, before a matchday is complete.
it('holds back places and penalties of an incomplete matchday (MD-02)', function () {
    $season = leagueSeason();
    enterPoints($season, 1, ['Anna' => 40, 'Bert' => 60]);

    $this->get(route('home'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('matchdays.0.complete', false)
            ->where('matchdays.0.hasPoints', true)
            ->where('matchdays.0.rows.0.place', null)
            ->where('matchdays.0.rows.0.penaltyCents', null)
            ->where('standings.0.points', 0)
            ->where('standings.0.penaltyCents', 0));
});

// MD-04 — places, penalties and the table follow a change of points.
it('follows a change of points (MD-04)', function () {
    $season = leagueSeason();
    enterPoints($season, 1, ['Anna' => 40, 'Bert' => 60, 'Cleo' => 50]);

    $ids = Player::pluck('id', 'name');

    $this->actingAs(member())->put(route('matchdays.update', ['season' => $season, 'matchday' => 1]), [
        'points' => [$ids['Anna'] => 90, $ids['Bert'] => 10, $ids['Cleo'] => 20],
    ]);

    $this->get(route('home'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('standings.0.name', 'Anna')
            ->where('standings.0.points', 90)
            ->where('standings.0.penaltyCents', 350)
            ->where('matchdays.0.rows.0.name', 'Anna')
            ->where('matchdays.0.rows.0.place', 1));
});
