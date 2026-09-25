<?php

declare(strict_types=1);

use App\Models\Player;
use App\Models\Score;
use App\Models\Season;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia;

describe('PLY-01 · an admin creates a player', function () {
    it('stores the name and the kicker Manager alias', function () {
        $this->actingAs(admin())->post(route('players.store'), ['name' => 'Paul', 'alias' => 'PaulKicker'])
            ->assertRedirect(route('players.index'));

        expect(Player::sole()->only(['name', 'alias']))->toBe(['name' => 'Paul', 'alias' => 'PaulKicker']);
    });

    it('needs both a name and an alias', function () {
        $this->actingAs(admin())->post(route('players.store'), ['name' => '', 'alias' => ''])
            ->assertInvalid(['name', 'alias']);
    });

    it('lists the players by name A–Z (D3)', function () {
        Player::factory()->create(['name' => 'Schwabe', 'alias' => 's']);
        Player::factory()->create(['name' => 'BK', 'alias' => 'b']);

        $this->actingAs(admin())->get(route('players.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('players/index')
                ->where('players.0.name', 'BK')
                ->where('players.1.name', 'Schwabe'));
    });
});

describe('D8 · no duplicate players', function () {
    it('refuses a second player with the same name and alias', function () {
        Player::factory()->create(['name' => 'Paul', 'alias' => 'paul_kicker']);

        $this->actingAs(admin())->post(route('players.store'), ['name' => 'Paul', 'alias' => 'paul_kicker'])
            ->assertInvalid(['name' => 'Einen Mitspieler mit diesem Namen und Alias gibt es schon.']);

        expect(Player::count())->toBe(1);
    });

    it('allows the same name with another alias', function () {
        Player::factory()->create(['name' => 'Paul', 'alias' => 'paul_kicker']);

        $this->actingAs(admin())->post(route('players.store'), ['name' => 'Paul', 'alias' => 'paul2'])
            ->assertValid();

        expect(Player::count())->toBe(2);
    });
});

// ACC-04 — a player needs no account; players and users are separate.
it('creates no user account for a player (ACC-04)', function () {
    $admin = admin();

    $this->actingAs($admin)->post(route('players.store'), ['name' => 'Paul', 'alias' => 'p']);

    expect(User::count())->toBe(1)
        ->and(Schema::getColumnListing('players'))->not->toContain('user_id');
});

describe('ACC-03 · only admins create players', function () {
    it('refuses a plain user', function () {
        $this->actingAs(member())->get(route('players.index'))->assertForbidden();
        $this->actingAs(member())->post(route('players.store'), ['name' => 'Paul', 'alias' => 'p'])->assertForbidden();

        expect(Player::count())->toBe(0);
    });

    it('sends a guest to the login', function () {
        $this->post(route('players.store'), ['name' => 'Paul', 'alias' => 'p'])->assertRedirect(route('login'));
    });

    it('offers no route to delete a player', function () {
        $player = Player::factory()->create();

        $this->actingAs(admin())->delete("/players/{$player->id}")->assertMethodNotAllowed();

        expect(Player::count())->toBe(1);
    });
});

describe('PLY-02 · an admin changes a player’s name and alias', function () {
    it('opens the form with the current name and alias', function () {
        $player = Player::factory()->create(['name' => 'BK', 'alias' => 'bk_kicker']);

        $this->actingAs(admin())->get(route('players.edit', $player))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('players/edit')
                ->where('player', ['id' => $player->id, 'name' => 'BK', 'alias' => 'bk_kicker']));
    });

    it('stores the new name and alias', function () {
        $player = Player::factory()->create(['name' => 'BK', 'alias' => 'bk_kicker']);

        $this->actingAs(admin())->put(route('players.update', $player), ['name' => 'Bernd', 'alias' => 'bernd_k'])
            ->assertRedirect(route('players.index'));

        expect($player->fresh()?->only(['name', 'alias']))->toBe(['name' => 'Bernd', 'alias' => 'bernd_k']);
    });

    it('shows the new name in every season, earlier ones too, with points and penalties unchanged', function () {
        $player = Player::factory()->create(['name' => 'BK', 'alias' => 'bk']);
        $other = Player::factory()->create(['name' => 'FK', 'alias' => 'fk']);
        $old = Season::factory()->create(['name' => '2024/25']);
        Season::factory()->create(['name' => '2025/26']);
        $old->players()->sync([$player->id, $other->id]);
        Score::create(['season_id' => $old->id, 'player_id' => $player->id, 'matchday' => 1, 'points' => 40]);
        Score::create(['season_id' => $old->id, 'player_id' => $other->id, 'matchday' => 1, 'points' => 60]);

        $before = $this->get(route('seasons.show', $old))->viewData('page')['props']['standings'];

        $this->actingAs(admin())->put(route('players.update', $player), ['name' => 'Bernd', 'alias' => 'bk']);
        auth()->logout();

        $after = $this->get(route('seasons.show', $old))->viewData('page')['props']['standings'];

        $renamed = collect($after)->firstWhere('playerId', $player->id);
        expect($renamed['name'])->toBe('Bernd')
            ->and(collect($after)->map(fn (array $row) => array_diff_key($row, ['name' => 1]))->all())
            ->toBe(collect($before)->map(fn (array $row) => array_diff_key($row, ['name' => 1]))->all());
    });

    it('keeps a name and alias unique among the other players (D8)', function () {
        Player::factory()->create(['name' => 'Paul', 'alias' => 'paul_kicker']);
        $player = Player::factory()->create(['name' => 'Peter', 'alias' => 'peter']);

        $this->actingAs(admin())->put(route('players.update', $player), ['name' => 'Paul', 'alias' => 'paul_kicker'])
            ->assertInvalid(['name' => 'Einen Mitspieler mit diesem Namen und Alias gibt es schon.']);
    });

    it('lets a player keep their own name and alias', function () {
        $player = Player::factory()->create(['name' => 'Paul', 'alias' => 'paul_kicker']);

        $this->actingAs(admin())->put(route('players.update', $player), ['name' => 'Paul', 'alias' => 'paul_kicker'])
            ->assertValid();
    });

    it('needs both a name and an alias', function () {
        $player = Player::factory()->create();

        $this->actingAs(admin())->put(route('players.update', $player), ['name' => '', 'alias' => ''])
            ->assertInvalid(['name', 'alias']);
    });

    it('refuses a plain user and sends a guest to the login', function () {
        $player = Player::factory()->create(['name' => 'BK']);

        $this->actingAs(member())->get(route('players.edit', $player))->assertForbidden();
        $this->actingAs(member())->put(route('players.update', $player), ['name' => 'X', 'alias' => 'x'])->assertForbidden();
        auth()->logout();
        $this->put(route('players.update', $player), ['name' => 'X', 'alias' => 'x'])->assertRedirect(route('login'));

        expect($player->fresh()?->name)->toBe('BK');
    });
});
