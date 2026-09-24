<?php

declare(strict_types=1);

use App\Models\Player;
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

    it('offers no route to edit or delete a player', function () {
        $player = Player::factory()->create();

        $this->actingAs(admin())->patch("/players/{$player->id}", ['name' => 'X'])->assertNotFound();
        $this->actingAs(admin())->delete("/players/{$player->id}")->assertNotFound();
    });
});
