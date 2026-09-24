<?php

declare(strict_types=1);

use App\Domain\Users\Role;
use App\Http\Users\StoreUser\UserInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia;

describe('B14 · an admin creates a user', function () {
    it('shows the form with the roles to choose from', function () {
        $this->actingAs(admin())->get(route('users.create'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('settings/users/create')
                ->where('roles', ['admin', 'user']));
    });

    it('creates the user with the chosen role and sends the invitation', function () {
        Notification::fake();

        $this->actingAs(admin())->post(route('users.store'), [
            'name' => 'Nora Neu',
            'email' => 'Nora@Example.com',
            'role' => 'user',
        ])->assertRedirect(route('users.index'));

        $user = User::firstWhere('email', 'nora@example.com');

        expect($user)->not->toBeNull()
            ->and($user?->name)->toBe('Nora Neu')
            ->and($user?->assignedRole())->toBe(Role::User)
            ->and($user?->email_verified_at)->toBeNull();

        Notification::assertSentTo($user, UserInvitation::class);
    });

    it('lets the invited user set a password, which verifies the address', function () {
        Notification::fake();

        $this->actingAs(admin())->post(route('users.store'), [
            'name' => 'Nora Neu',
            'email' => 'nora@example.com',
            'role' => 'user',
        ]);
        auth()->logout();

        $user = User::firstWhere('email', 'nora@example.com');

        Notification::assertSentTo($user, UserInvitation::class, function (UserInvitation $invitation) use ($user) {
            $mail = $invitation->toMail($user);
            $token = str($mail->actionUrl)->between('reset-password/', '?')->toString();

            $this->post(route('password.update'), [
                'token' => $token,
                'email' => 'nora@example.com',
                'password' => 'a-new-password',
                'password_confirmation' => 'a-new-password',
            ])->assertSessionHasNoErrors();

            return true;
        });

        expect($user?->fresh()?->email_verified_at)->not->toBeNull();
        $this->post(route('login.store'), ['email' => 'nora@example.com', 'password' => 'a-new-password']);
        $this->assertAuthenticatedAs($user);
    });

    it('requires a name, a valid address and a role', function () {
        $this->actingAs(admin())->post(route('users.store'), [
            'name' => '',
            'email' => 'not-an-address',
            'role' => 'owner',
        ])->assertInvalid(['name', 'email', 'role']);
    });

    it('refuses an address an active user has', function () {
        member(['email' => 'taken@example.com']);

        $this->actingAs(admin())->post(route('users.store'), [
            'name' => 'Second',
            'email' => 'taken@example.com',
            'role' => 'user',
        ])->assertInvalid(['email']);
    });

    it('B15 · refuses a deleted user’s address and points to restoring them', function () {
        member(['email' => 'gone@example.com'])->delete();

        $this->actingAs(admin())->post(route('users.store'), [
            'name' => 'Second',
            'email' => 'gone@example.com',
            'role' => 'user',
        ])->assertInvalid(['email' => __('This email address belongs to a deleted user. Restore that user instead.')]);
    });

    it('B13 · refuses a user without the permission', function () {
        $this->actingAs(member())->get(route('users.create'))->assertForbidden();
        $this->actingAs(member())->post(route('users.store'), [
            'name' => 'X', 'email' => 'x@example.com', 'role' => 'admin',
        ])->assertForbidden();

        expect(User::firstWhere('email', 'x@example.com'))->toBeNull();
    });
});
