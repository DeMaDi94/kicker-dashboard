<?php

declare(strict_types=1);

use App\Models\Player;
use App\Models\Score;
use App\Models\Season;
use Inertia\Testing\AssertableInertia;

/**
 * A season of Anna, Bert and Cleo with three complete matchdays; the same
 * players across seasons, so a deleted season leaves a player page behind.
 *
 * @return array{Season, Player}
 */
function seasonWithScores(string $name): array
{
    $season = Season::factory()->create(['name' => $name]);
    $players = array_map(fn (string $player): Player => Player::firstOrCreate(['name' => $player], ['alias' => strtolower($player)]), ['Anna', 'Bert', 'Cleo']);
    $season->players()->attach(array_map(fn (Player $player): int => $player->id, $players));

    foreach ([1 => [50, 40, 30], 2 => [20, 60, 60], 3 => [70, 10, 40]] as $matchday => $points) {
        foreach ($players as $i => $player) {
            Score::create(['season_id' => $season->id, 'player_id' => $player->id, 'matchday' => $matchday, 'points' => $points[$i]]);
        }
    }

    return [$season, $players[0]];
}

describe('SEA-06 · an admin deletes a season', function () {
    it('soft-deletes the season and returns to the season view with a toast', function () {
        [$season] = seasonWithScores('2026/27');

        $this->actingAs(admin())->delete(route('seasons.destroy', $season))
            ->assertRedirect(route('home'))
            ->assertInertiaFlash('toast.message', __('Season :name deleted.', ['name' => '2026/27']));

        $this->assertSoftDeleted($season);
    });

    it('keeps the deleted season’s points', function () {
        [$season] = seasonWithScores('2026/27');

        $this->actingAs(admin())->delete(route('seasons.destroy', $season));

        expect(Score::where('season_id', $season->id)->count())->toBe(9);
    });

    it('removes the season from the season view and its choice', function () {
        [$kept] = seasonWithScores('2025/26');
        [$gone] = seasonWithScores('2026/27');
        $gone->delete();

        $this->get(route('home'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('season.id', $kept->id)
                ->where('seasons', [['id' => $kept->id, 'name' => '2025/26']]));

        $this->get(route('seasons.show', $gone))->assertNotFound();
        $this->get(route('seasons.compare', $gone))->assertNotFound();
        $this->actingAs(member())->get(route('matchdays.edit', ['season' => $gone->id, 'matchday' => 1]))->assertNotFound();
    });

    it('removes the season from the statistics', function () {
        [$kept, $anna] = seasonWithScores('2025/26');
        [$gone] = seasonWithScores('2026/27');
        $gone->delete();

        $this->get(route('players.show', $anna))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('season.id', $kept->id)
                ->where('seasons', [['id' => $kept->id, 'name' => '2025/26']])
                ->where('career.seasonsPlayed', 1));
        $this->get(route('players.show', ['player' => $anna, 'season' => $gone->id]))->assertNotFound();

        $this->get(route('records'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('seasons', [['id' => $kept->id, 'name' => '2025/26']])
                ->has('records.highestScore.holders', 1)
                ->where('records.highestScore.holders.0.seasonName', '2025/26'));
        $this->get(route('records', ['season' => $gone->id]))->assertNotFound();
    });

    it('is refused to a plain user and to a guest', function () {
        $season = Season::factory()->create();

        $this->actingAs(member())->delete(route('seasons.destroy', $season))->assertForbidden();
        $this->actingAs(member())->post(route('seasons.restore', $season))->assertForbidden();
        $this->actingAs(member())->get(route('seasons.deleted'))->assertForbidden();

        expect($season->fresh()?->trashed())->toBeFalse();
    });

    it('offers the delete to admins only, through the shared permissions', function () {
        Season::factory()->create();

        $this->actingAs(admin())->get(route('home'))
            ->assertInertia(fn (AssertableInertia $page) => $page->where('auth.permissions', fn ($permissions) => collect($permissions)->contains('seasons.delete')));
        $this->actingAs(member())->get(route('home'))
            ->assertInertia(fn (AssertableInertia $page) => $page->where('auth.permissions', []));
    });
});

describe('SEA-06 · an admin restores a deleted season', function () {
    it('lists the deleted seasons, the one created last first', function () {
        $older = Season::factory()->create(['name' => '2024/25']);
        $newer = Season::factory()->create(['name' => '2025/26']);
        Season::factory()->create(['name' => '2026/27']);
        $older->delete();
        $newer->delete();

        $this->actingAs(admin())->get(route('seasons.deleted'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('seasons/deleted')
                ->where('seasons', [['id' => $newer->id, 'name' => '2025/26'], ['id' => $older->id, 'name' => '2024/25']]));
    });

    it('brings the season back with its points', function () {
        [$season] = seasonWithScores('2026/27');
        $season->delete();

        $this->actingAs(admin())->from(route('seasons.deleted'))->post(route('seasons.restore', $season))
            ->assertRedirect(route('seasons.deleted'))
            ->assertInertiaFlash('toast.message', __('Season :name restored.', ['name' => '2026/27']));

        expect($season->fresh()?->trashed())->toBeFalse();

        $this->get(route('seasons.show', $season))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('season.id', $season->id)->has('standings', 3));
    });
});

describe('D8 · a deleted season frees its name', function () {
    it('lets a new season take the name', function () {
        $old = Season::factory()->create(['name' => '2026/27']);
        $this->actingAs(admin())->delete(route('seasons.destroy', $old));

        $this->actingAs(admin())->post(route('seasons.store'), [
            'name' => '2026/27', 'penalty_start' => 5, 'penalty_step' => 0.5, 'player_ids' => [],
        ])->assertValid();

        expect(Season::where('name', '2026/27')->count())->toBe(1)
            ->and(Season::onlyTrashed()->where('name', '2026/27')->count())->toBe(1);
    });

    it('refuses to restore a season while another carries its name', function () {
        $old = Season::factory()->create(['name' => '2026/27']);
        $old->delete();
        Season::factory()->create(['name' => '2026/27']);

        $this->actingAs(admin())->post(route('seasons.restore', $old->id))
            ->assertRedirect()
            ->assertInertiaFlash('toast.type', 'error');

        expect($old->fresh()?->trashed())->toBeTrue();
    });
});
