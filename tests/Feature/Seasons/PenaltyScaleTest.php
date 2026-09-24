<?php

declare(strict_types=1);

use App\Models\Player;
use App\Models\Score;
use App\Models\Season;
use Inertia\Testing\AssertableInertia;

describe('SEA-05 · any signed-in user changes the penalty scale', function () {
    it('lets a plain user change start amount and step', function () {
        $season = Season::factory()->create(['penalty_start_cents' => 450, 'penalty_step_cents' => 50]);

        $this->actingAs(member())->get(route('seasons.penalty-scale.edit', $season))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('seasons/penalty-scale')
                ->where('season.penaltyStartCents', 450)
                ->where('season.penaltyStepCents', 50));

        $this->actingAs(member())->put(route('seasons.penalty-scale.update', $season), ['penalty_start' => 3, 'penalty_step' => 1.25])
            ->assertRedirect(route('seasons.show', $season));

        expect($season->fresh()?->only(['penalty_start_cents', 'penalty_step_cents']))
            ->toBe(['penalty_start_cents' => 300, 'penalty_step_cents' => 125]);
    });

    it('sends a guest to the login', function () {
        $season = Season::factory()->create(['penalty_start_cents' => 450, 'penalty_step_cents' => 50]);

        $this->get(route('seasons.penalty-scale.edit', $season))->assertRedirect(route('login'));
        $this->put(route('seasons.penalty-scale.update', $season), ['penalty_start' => 3, 'penalty_step' => 1])
            ->assertRedirect(route('login'));

        expect($season->fresh()?->penalty_start_cents)->toBe(450);
    });

    // PEN-01 — no amount below 0 €; amounts are euros to the cent.
    it('refuses missing and negative amounts and fractions of a cent', function () {
        $season = Season::factory()->create();

        $this->actingAs(member())->put(route('seasons.penalty-scale.update', $season), ['penalty_start' => -1, 'penalty_step' => 0.505])
            ->assertInvalid(['penalty_start', 'penalty_step']);
        $this->actingAs(member())->put(route('seasons.penalty-scale.update', $season), [])
            ->assertInvalid(['penalty_start', 'penalty_step']);
    });

    it('refuses an amount its cents column cannot hold', function () {
        $season = Season::factory()->create();

        $this->actingAs(member())->put(route('seasons.penalty-scale.update', $season), ['penalty_start' => 50000000, 'penalty_step' => 0.5])
            ->assertInvalid(['penalty_start']);
    });

    it('applies the new values to every matchday of the season, points already entered included', function () {
        $season = Season::factory()->create(['penalty_start_cents' => 450, 'penalty_step_cents' => 50]);
        $players = [];
        foreach (['Anna', 'Bert', 'Cleo'] as $name) {
            $players[$name] = Player::factory()->create(['name' => $name])->id;
        }
        $season->players()->attach(array_values($players));

        foreach ([1 => ['Anna' => 40, 'Bert' => 60, 'Cleo' => 55], 2 => ['Anna' => 70, 'Bert' => 20, 'Cleo' => 30]] as $matchday => $points) {
            foreach ($points as $name => $value) {
                Score::create(['season_id' => $season->id, 'player_id' => $players[$name], 'matchday' => $matchday, 'points' => $value]);
            }
        }

        $this->actingAs(member())->put(route('seasons.penalty-scale.update', $season), ['penalty_start' => 3, 'penalty_step' => 1])
            ->assertRedirect();

        $this->get(route('seasons.show', $season))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('season.penaltyStartCents', 300)
                ->where('season.penaltyStepCents', 100)
                ->where('matchdays.0.rows.0.name', 'Bert')->where('matchdays.0.rows.0.penaltyCents', 100)
                ->where('matchdays.0.rows.1.name', 'Cleo')->where('matchdays.0.rows.1.penaltyCents', 200)
                ->where('matchdays.0.rows.2.name', 'Anna')->where('matchdays.0.rows.2.penaltyCents', 300)
                ->where('matchdays.1.rows.0.name', 'Anna')->where('matchdays.1.rows.0.penaltyCents', 100)
                ->where('matchdays.1.rows.2.name', 'Bert')->where('matchdays.1.rows.2.penaltyCents', 300)
                ->where('standings.0.name', 'Anna')->where('standings.0.penaltyCents', 400)
                ->where('standings.1.name', 'Cleo')->where('standings.1.penaltyCents', 400)
                ->where('standings.2.name', 'Bert')->where('standings.2.penaltyCents', 400));
    });
});
