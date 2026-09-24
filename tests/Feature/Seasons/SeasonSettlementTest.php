<?php

declare(strict_types=1);

use App\Models\Player;
use App\Models\Score;
use App\Models\Season;
use Inertia\Testing\AssertableInertia;

describe('PEN-04 · the interim settlement', function () {
    it('is set when the season is created', function () {
        $this->actingAs(admin())->post(route('seasons.store'), [
            'name' => '2026/27', 'penalty_start' => 5, 'penalty_step' => 0.5, 'settlement_matchday' => 16, 'player_ids' => [],
        ])->assertRedirect();

        expect(Season::sole()->settlement_matchday)->toBe(16);
    });

    it('may be left out when the season is created', function () {
        $this->actingAs(admin())->post(route('seasons.store'), [
            'name' => '2026/27', 'penalty_start' => 5, 'penalty_step' => 0.5, 'settlement_matchday' => null, 'player_ids' => [],
        ])->assertRedirect();

        expect(Season::sole()->settlement_matchday)->toBeNull();
    });

    it('follows any matchday but the last', function (int $matchday) {
        $this->actingAs(admin())->post(route('seasons.store'), [
            'name' => '2026/27', 'penalty_start' => 5, 'penalty_step' => 0.5, 'settlement_matchday' => $matchday, 'player_ids' => [],
        ])->assertInvalid(['settlement_matchday']);
    })->with([0, 34]);

    it('can be changed and removed later by an admin', function () {
        $season = Season::factory()->create(['settlement_matchday' => 16]);

        $this->actingAs(admin())->get(route('seasons.settlement.edit', $season))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('seasons/settlement')
                ->where('season.settlementMatchday', 16)
                ->has('matchdays', 33));

        $this->actingAs(admin())->put(route('seasons.settlement.update', $season), ['settlement_matchday' => 17])
            ->assertRedirect(route('seasons.show', $season));
        expect($season->fresh()?->settlement_matchday)->toBe(17);

        $this->actingAs(admin())->put(route('seasons.settlement.update', $season), ['settlement_matchday' => null]);
        expect($season->fresh()?->settlement_matchday)->toBeNull();
    });

    it('is refused to a plain user (ACC-03)', function () {
        $season = Season::factory()->create();

        $this->actingAs(member())->get(route('seasons.settlement.edit', $season))->assertForbidden();
        $this->actingAs(member())->put(route('seasons.settlement.update', $season), ['settlement_matchday' => 5])->assertForbidden();

        expect($season->fresh()?->settlement_matchday)->toBeNull();
    });

    it('splits the penalties in the overall table and leaves the places alone', function () {
        $season = Season::factory()->create(['penalty_start_cents' => 450, 'penalty_step_cents' => 50, 'settlement_matchday' => 1]);
        $anna = Player::factory()->create(['name' => 'Anna']);
        $bert = Player::factory()->create(['name' => 'Bert']);
        $season->players()->attach([$anna->id, $bert->id]);

        foreach ([1 => [$anna->id => 10, $bert->id => 20], 2 => [$anna->id => 30, $bert->id => 5]] as $matchday => $points) {
            foreach ($points as $player => $value) {
                Score::create(['season_id' => $season->id, 'player_id' => $player, 'matchday' => $matchday, 'points' => $value]);
            }
        }

        $this->get(route('seasons.show', $season))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('season.settlementMatchday', 1)
                ->where('standings.0.name', 'Anna')->where('standings.0.place', 1)->where('standings.0.points', 40)
                ->where('standings.0.firstHalfPenaltyCents', 450)
                ->where('standings.0.secondHalfPenaltyCents', 400)
                ->where('standings.0.penaltyCents', 850)
                ->where('standings.1.name', 'Bert')->where('standings.1.place', 2)
                ->where('standings.1.firstHalfPenaltyCents', 400)
                ->where('standings.1.secondHalfPenaltyCents', 450));
    });
});
