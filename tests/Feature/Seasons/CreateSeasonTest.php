<?php

declare(strict_types=1);

use App\Models\Player;
use App\Models\Season;
use Inertia\Testing\AssertableInertia;

describe('SEA-01 · an admin creates a season', function () {
    it('stores the name and the penalty scale in cents', function () {
        $this->actingAs(admin())->post(route('seasons.store'), [
            'name' => '2026/27',
            'penalty_start' => 5,
            'penalty_step' => 0.5,
            'player_ids' => [],
        ])->assertRedirect(route('seasons.show', Season::sole()));

        expect(Season::sole()->only(['name', 'penalty_start_cents', 'penalty_step_cents']))
            ->toBe(['name' => '2026/27', 'penalty_start_cents' => 500, 'penalty_step_cents' => 50]);
    });

    // PEN-01 — no amount below 0 €; amounts are euros to the cent.
    it('refuses negative amounts and fractions of a cent', function () {
        $this->actingAs(admin())->post(route('seasons.store'), [
            'name' => '2026/27',
            'penalty_start' => -1,
            'penalty_step' => 0.505,
            'player_ids' => [],
        ])->assertInvalid(['penalty_start', 'penalty_step']);
    });

    it('refuses an amount its cents column cannot hold', function () {
        $this->actingAs(admin())->post(route('seasons.store'), [
            'name' => '2026/27', 'penalty_start' => 50000000, 'penalty_step' => 0.5, 'player_ids' => [],
        ])->assertInvalid(['penalty_start']);
    });

    // D8 — no two seasons share a name.
    it('refuses a name another season has', function () {
        Season::factory()->create(['name' => '2026/27']);

        $this->actingAs(admin())->post(route('seasons.store'), ['name' => '2026/27', 'penalty_start' => 5, 'penalty_step' => 0.5, 'player_ids' => []])
            ->assertInvalid(['name']);

        expect(Season::count())->toBe(1);
    });

    it('needs a name', function () {
        $this->actingAs(admin())->post(route('seasons.store'), ['name' => '', 'penalty_start' => 1, 'penalty_step' => 1, 'player_ids' => []])
            ->assertInvalid(['name']);
    });
});

// SEA-02 — the players of the season are chosen with it.
it('takes the chosen players into the season (SEA-02)', function () {
    [$a, $b] = Player::factory()->count(2)->create();

    $this->actingAs(admin())->get(route('seasons.create'))
        ->assertInertia(fn (AssertableInertia $page) => $page->component('seasons/create')->has('players', 2));

    $this->actingAs(admin())->post(route('seasons.store'), [
        'name' => '2026/27', 'penalty_start' => 4.5, 'penalty_step' => 0.5, 'player_ids' => [$a->id],
    ]);

    expect(Season::sole()->players()->pluck('players.id')->all())->toBe([$a->id]);
});

describe('ACC-03 · only admins create seasons', function () {
    it('refuses a plain user', function () {
        $this->actingAs(member())->get(route('seasons.create'))->assertForbidden();
        $this->actingAs(member())->post(route('seasons.store'), ['name' => 'x', 'penalty_start' => 1, 'penalty_step' => 1, 'player_ids' => []])
            ->assertForbidden();

        expect(Season::count())->toBe(0);
    });

    it('offers no route to edit or delete a season', function () {
        $season = Season::factory()->create();

        $this->actingAs(admin())->patch("/seasons/{$season->id}", ['name' => 'X'])->assertStatus(405);
        $this->actingAs(admin())->delete("/seasons/{$season->id}")->assertStatus(405);
    });
});
