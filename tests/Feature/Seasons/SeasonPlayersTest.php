<?php

declare(strict_types=1);

use App\Models\Player;
use App\Models\Score;
use App\Models\Season;
use Inertia\Testing\AssertableInertia;

describe('SEA-02 · an admin sets the players of a season', function () {
    it('replaces the players of the season', function () {
        $season = Season::factory()->create();
        [$a, $b] = Player::factory()->count(2)->create();
        $season->players()->attach($a);

        $this->actingAs(admin())->put(route('seasons.players.update', $season), ['player_ids' => [$b->id]])
            ->assertRedirect(route('seasons.show', $season));

        expect($season->players()->pluck('players.id')->all())->toBe([$b->id]);
    });

    it('refuses a plain user (ACC-03)', function () {
        $season = Season::factory()->create();

        $this->actingAs(member())->get(route('seasons.players.edit', $season))->assertForbidden();
        $this->actingAs(member())->put(route('seasons.players.update', $season), ['player_ids' => []])->assertForbidden();
    });
});

describe('SEA-03 · the players are fixed from the first point on', function () {
    it('is open while no point is entered', function () {
        $season = Season::factory()->create();

        $this->actingAs(admin())->get(route('seasons.players.edit', $season))
            ->assertInertia(fn (AssertableInertia $page) => $page->component('seasons/players')->where('locked', false));
    });

    it('refuses a change once a point is entered', function () {
        $season = Season::factory()->create();
        [$a, $b] = Player::factory()->count(2)->create();
        $season->players()->attach($a);
        Score::create(['season_id' => $season->id, 'player_id' => $a->id, 'matchday' => 1, 'points' => 40]);

        $this->actingAs(admin())->get(route('seasons.players.edit', $season))
            ->assertInertia(fn (AssertableInertia $page) => $page->where('locked', true)->where('selected', [$a->id]));

        $this->actingAs(admin())->put(route('seasons.players.update', $season), ['player_ids' => [$a->id, $b->id]])
            ->assertInvalid(['player_ids']);

        expect($season->players()->pluck('players.id')->all())->toBe([$a->id]);
    });
});
