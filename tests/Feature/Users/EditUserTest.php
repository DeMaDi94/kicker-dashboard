<?php

declare(strict_types=1);

use App\Models\User;
use Inertia\Testing\AssertableInertia;

describe('B16 · the edit screen', function () {
    it('shows the user and no refusal when both actions are allowed', function () {
        $actor = admin();
        $user = member(['name' => 'Emil']);

        $this->actingAs($actor)->get(route('users.edit', $user))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('settings/users/edit')
                ->where('user.name', 'Emil')
                ->where('user.role', 'user')
                ->where('roles', ['admin', 'user'])
                ->where('deleteRefusal', null)
                ->where('demoteRefusal', null));
    });

    it('B15 · says why an admin cannot delete or demote themselves', function () {
        $actor = admin();
        admin();

        $this->actingAs($actor)->get(route('users.edit', $actor))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('deleteRefusal', __('You cannot delete your own account here.'))
                ->where('demoteRefusal', __('You cannot remove the admin role from yourself.')));
    });

    it('does not find an unknown or a deleted user', function () {
        $actor = admin();
        $gone = member();
        $gone->delete();

        $this->actingAs($actor)->get(route('users.edit', 999))->assertNotFound();
        $this->actingAs($actor)->get(route('users.edit', $gone))->assertNotFound();
    });

    it('B13 · refuses a user without the permission', function () {
        $this->actingAs(member())->get(route('users.edit', member()))->assertForbidden();
    });
});

describe('B16 · an admin updates a user', function () {
    it('changes the name and the role, not the address', function () {
        $user = member(['email' => 'keep@example.com']);

        $this->actingAs(admin())->patch(route('users.update', $user), [
            'name' => 'Renamed',
            'role' => 'admin',
            'email' => 'other@example.com',
        ])->assertRedirect(route('users.edit', $user));

        $user = User::findOrFail($user->id);
        expect($user->name)->toBe('Renamed')
            ->and($user->email)->toBe('keep@example.com')
            ->and($user->getRoleNames()->all())->toBe(['admin']);
    });

    it('requires a name and a known role', function () {
        $this->actingAs(admin())->patch(route('users.update', member()), [
            'name' => '',
            'role' => 'owner',
        ])->assertInvalid(['name', 'role']);
    });

    it('B15 · refuses an admin removing their own admin role', function () {
        $actor = admin();
        admin();

        $this->actingAs($actor)->patch(route('users.update', $actor), [
            'name' => $actor->name,
            'role' => 'user',
        ])->assertInvalid(['role' => __('You cannot remove the admin role from yourself.')]);

        expect($actor->fresh()?->getRoleNames()->all())->toBe(['admin']);
    });

    it('lets one admin demote another while one remains', function () {
        $actor = admin();
        $other = admin();

        $this->actingAs($actor)->patch(route('users.update', $other), [
            'name' => $other->name,
            'role' => 'user',
        ])->assertSessionHasNoErrors();

        expect($other->fresh()?->getRoleNames()->all())->toBe(['user']);
    });

    it('does not find an unknown user', function () {
        $this->actingAs(admin())->patch(route('users.update', 999), ['name' => 'X', 'role' => 'user'])
            ->assertNotFound();
    });

    it('B13 · refuses a user without the permission', function () {
        $user = member(['name' => 'Same']);

        $this->actingAs(member())->patch(route('users.update', $user), ['name' => 'X', 'role' => 'admin'])
            ->assertForbidden();

        expect($user->fresh()?->name)->toBe('Same');
    });
});
