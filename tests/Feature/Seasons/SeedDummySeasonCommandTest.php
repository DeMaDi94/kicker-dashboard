<?php

declare(strict_types=1);

use App\Models\Player;
use App\Models\Score;
use App\Models\Season;

describe('SEA-04 · a dummy season for development', function () {
    it('fills all 34 matchdays for every player of the season', function () {
        $this->artisan('seasons:seed-dummy', ['--players' => 6])->assertSuccessful();

        $season = Season::sole();

        expect($season->name)->toBe('Dummy-Saison')
            ->and($season->players()->count())->toBe(6)
            ->and(Score::query()->where('season_id', $season->id)->count())->toBe(6 * 34)
            ->and(Score::query()->distinct()->pluck('matchday')->sort()->values()->all())->toBe(range(1, 34));
    });

    it('reuses the players there are before creating new ones', function () {
        Player::factory()->count(3)->create();

        $this->artisan('seasons:seed-dummy', ['--players' => 5])->assertSuccessful();

        expect(Player::count())->toBe(5);
    });

    it('refuses a name that is taken (D8)', function () {
        Season::factory()->create(['name' => 'Dummy-Saison']);

        $this->artisan('seasons:seed-dummy')->assertFailed();

        expect(Season::count())->toBe(1);
    });

    it('refuses to run in production', function () {
        app()->detectEnvironment(fn () => 'production');

        $this->artisan('seasons:seed-dummy')->assertFailed();

        expect(Season::count())->toBe(0);
    });
});
