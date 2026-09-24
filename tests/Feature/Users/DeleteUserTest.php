<?php

declare(strict_types=1);

use App\Models\Passkey;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

describe('B15 · an admin deletes a user', function () {
    it('soft-deletes the user', function () {
        $user = member();

        $this->actingAs(admin())->delete(route('users.destroy', $user))
            ->assertRedirect(route('users.index'));

        $this->assertSoftDeleted($user);
    });

    it('ends the deleted user’s session and refuses their login', function () {
        $user = member();
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password']);
        $this->assertAuthenticatedAs($user);
        $session = session()->all();

        $user->delete();
        Auth::forgetGuards();

        $this->withSession($session)->get(route('dashboard'))->assertRedirect(route('login'));

        Auth::forgetGuards();
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertInvalid(['email']);
        $this->assertGuest();
    });

    it('hides a deleted user’s passkeys, so they cannot sign in with one', function () {
        $user = member();
        Passkey::forceCreate(['user_id' => $user->id, 'name' => 'Key', 'credential_id' => 'abc', 'credential' => []]);

        expect(Passkey::where('credential_id', 'abc')->exists())->toBeTrue();

        $user->delete();

        expect(Passkey::where('credential_id', 'abc')->exists())->toBeFalse();
    });

    it('refuses an admin deleting their own account from the list', function () {
        $actor = admin();
        admin();

        $this->actingAs($actor)->delete(route('users.destroy', $actor))
            ->assertInvalid(['user' => __('You cannot delete your own account here.')]);

        expect($actor->fresh()?->trashed())->toBeFalse();
    });

    it('does not find an unknown or an already deleted user', function () {
        $actor = admin();
        $gone = member();
        $gone->delete();

        $this->actingAs($actor)->delete(route('users.destroy', 999))->assertNotFound();
        $this->actingAs($actor)->delete(route('users.destroy', $gone))->assertNotFound();
    });

    it('B13 · refuses a user without the permission', function () {
        $user = member();

        $this->actingAs(member())->delete(route('users.destroy', $user))->assertForbidden();

        expect(User::find($user->id))->not->toBeNull();
    });
});

describe('B15 · an admin restores a user', function () {
    it('brings the user back with their role', function () {
        $user = member();
        $user->delete();

        $this->actingAs(admin())->post(route('users.restore', $user))->assertRedirect();

        expect(User::find($user->id)?->getRoleNames()->all())->toBe(['user']);
    });

    it('does not find an unknown user', function () {
        $this->actingAs(admin())->post(route('users.restore', 999))->assertNotFound();
    });

    it('B13 · refuses a user without the permission', function () {
        $user = member();
        $user->delete();

        $this->actingAs(member())->post(route('users.restore', $user))->assertForbidden();

        $this->assertSoftDeleted($user);
    });
});
