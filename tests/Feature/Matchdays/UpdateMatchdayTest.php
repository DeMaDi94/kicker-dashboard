<?php

declare(strict_types=1);

use App\Models\Player;
use App\Models\Score;
use App\Models\Season;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia;

/**
 * @return array{Season, Collection<int, Player>}
 */
function seasonWithPlayers(int $count = 3): array
{
    $season = Season::factory()->create();
    $players = Player::factory()->count($count)->create();
    $season->players()->attach($players);

    return [$season, $players];
}

describe('MD-01 · entering the points of a matchday', function () {
    it('shows every player of the season with the points so far', function () {
        [$season, $players] = seasonWithPlayers(2);

        $this->actingAs(member())->get(route('matchdays.edit', ['season' => $season, 'matchday' => 1]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('matchdays/edit')
                ->has('players', 2)
                ->where('players.0.points', null)
                ->has('matchdays', 34));
    });

    it('stores points for every player, negative ones included', function () {
        [$season, $players] = seasonWithPlayers(2);

        $this->actingAs(member())->put(route('matchdays.update', ['season' => $season, 'matchday' => 3]), [
            'points' => [$players[0]->id => 55, $players[1]->id => -4],
        ])->assertRedirect(route('seasons.show', ['season' => $season, 'matchday' => 3]));

        expect(Score::where('matchday', 3)->orderBy('player_id')->pluck('points')->all())->toBe([55, -4]);
    });

    it('needs points for every player of the season', function () {
        [$season, $players] = seasonWithPlayers(2);

        $this->actingAs(member())->put(route('matchdays.update', ['season' => $season, 'matchday' => 1]), [
            'points' => [$players[0]->id => 55],
        ])->assertInvalid(["points.{$players[1]->id}"]);

        expect(Score::count())->toBe(0);
    });

    it('takes whole numbers only', function () {
        [$season, $players] = seasonWithPlayers(1);

        $this->actingAs(member())->put(route('matchdays.update', ['season' => $season, 'matchday' => 1]), [
            'points' => [$players[0]->id => 5.5],
        ])->assertInvalid(["points.{$players[0]->id}"]);
    });

    it('refuses points for a player outside the season', function () {
        [$season, $players] = seasonWithPlayers(1);
        $stranger = Player::factory()->create();

        $this->actingAs(member())->put(route('matchdays.update', ['season' => $season, 'matchday' => 1]), [
            'points' => [$players[0]->id => 10, $stranger->id => 20],
        ])->assertInvalid(['points']);
    });
});

// MD-04 — entered points can be changed.
it('changes entered points (MD-04)', function () {
    [$season, $players] = seasonWithPlayers(1);
    $url = route('matchdays.update', ['season' => $season, 'matchday' => 1]);

    $this->actingAs(member())->put($url, ['points' => [$players[0]->id => 10]]);
    $this->actingAs(member())->put($url, ['points' => [$players[0]->id => 12]]);

    expect(Score::sole()->points)->toBe(12);
});

// SEA-04 — a season has 34 matchdays.
it('has no matchday beyond the 34th (SEA-04)', function () {
    [$season] = seasonWithPlayers(1);

    $this->actingAs(member())->get(route('matchdays.edit', ['season' => $season, 'matchday' => 34]))->assertOk();
    $this->actingAs(member())->get("/seasons/{$season->id}/matchdays/35")->assertNotFound();
    $this->actingAs(member())->get("/seasons/{$season->id}/matchdays/0")->assertNotFound();
});

// ACC-02 — only signed-in users enter or change points.
it('sends a guest to the login (ACC-02)', function () {
    [$season, $players] = seasonWithPlayers(1);

    $this->get(route('matchdays.edit', ['season' => $season, 'matchday' => 1]))->assertRedirect(route('login'));
    $this->put(route('matchdays.update', ['season' => $season, 'matchday' => 1]), ['points' => [$players[0]->id => 1]])
        ->assertRedirect(route('login'));

    expect(Score::count())->toBe(0);
});
