<?php

declare(strict_types=1);

use App\Domain\Users\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia;

describe('D16 · the admin switches outgoing mail on and off', function () {
    it('starts switched off on a new installation', function () {
        expect(Setting::mailEnabled())->toBeFalse();

        $this->actingAs(admin())->get(route('mail-settings.edit'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('settings/mail')
                ->where('mailEnabled', false));
    });

    it('switches mail on and off again', function () {
        $admin = admin();

        $this->actingAs($admin)->patch(route('mail-settings.update'), ['mail_enabled' => '1'])
            ->assertRedirect(route('mail-settings.edit'))
            ->assertInertiaFlash('toast.message', __('Email settings saved.'));
        expect(Setting::mailEnabled())->toBeTrue();

        $this->actingAs($admin)->patch(route('mail-settings.update'), ['mail_enabled' => '0']);
        expect(Setting::mailEnabled())->toBeFalse();
    });

    it('requires the switch', function () {
        $this->actingAs(admin())->patch(route('mail-settings.update'), [])
            ->assertInvalid(['mail_enabled']);
    });

    it('B13 · refuses a user without the permission', function () {
        $this->actingAs(member())->get(route('mail-settings.edit'))->assertForbidden();
        $this->actingAs(member())->patch(route('mail-settings.update'), ['mail_enabled' => '1'])->assertForbidden();

        expect(Setting::mailEnabled())->toBeFalse();
    });

    it('tells every screen whether mail is on', function () {
        $this->actingAs(member())->get(route('profile.edit'))
            ->assertInertia(fn (AssertableInertia $page) => $page->where('mailEnabled', false));

        mailOn();

        $this->actingAs(member())->get(route('profile.edit'))
            ->assertInertia(fn (AssertableInertia $page) => $page->where('mailEnabled', true));
    });
});

describe('D16 · while outgoing mail is switched off', function () {
    beforeEach(fn () => Notification::fake());

    it('hides „Forgot password“ and refuses its routes', function () {
        $this->get(route('login'))
            ->assertInertia(fn (AssertableInertia $page) => $page->where('canResetPassword', false));
        $this->get(route('password.request'))->assertNotFound();
        $this->post(route('password.email'), ['email' => member()->email])->assertNotFound();

        mailOn();

        $this->get(route('login'))
            ->assertInertia(fn (AssertableInertia $page) => $page->where('canResetPassword', true));
        $this->get(route('password.request'))->assertOk();
    });

    it('sends no verification mail', function () {
        $this->actingAs(User::factory()->unverified()->create())
            ->post(route('verification.send'))
            ->assertNotFound();

        Notification::assertNothingSent();
    });

    it('B16 · sends no password reset link from the user list', function () {
        $this->actingAs(admin())->post(route('users.password-reset-link', member()))
            ->assertNotFound();

        Notification::assertNothingSent();
    });

    it('D15 · refuses the invitation and creates the user with a password', function () {
        $admin = admin();

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Nora Neu',
            'email' => 'nora@example.com',
            'role' => 'user',
            'password_setup' => 'invitation',
        ])->assertInvalid(['password_setup' => __('Email is switched off. Set a password instead.')]);
        expect(User::firstWhere('email', 'nora@example.com'))->toBeNull();

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Nora Neu',
            'email' => 'nora@example.com',
            'role' => 'user',
            'password_setup' => 'password',
            'password' => 'a-chosen-password',
            'password_confirmation' => 'a-chosen-password',
        ])->assertSessionHasNoErrors();

        expect(User::firstWhere('email', 'nora@example.com')?->email_verified_at)->not->toBeNull();
        Notification::assertNothingSent();
    });

    it('keeps a user’s own address, but not the rest of the profile', function () {
        $user = member(['name' => 'Old', 'email' => 'old@example.com']);

        $this->actingAs($user)->patch(route('profile.update'), ['name' => 'Old', 'email' => 'new@example.com'])
            ->assertInvalid(['email' => __('Your email address cannot be changed while email is switched off.')]);

        $this->actingAs($user)->patch(route('profile.update'), ['name' => 'New', 'email' => 'old@example.com'])
            ->assertSessionHasNoErrors();

        expect($user->fresh())
            ->name->toBe('New')
            ->email->toBe('old@example.com')
            ->email_verified_at->not->toBeNull();
    });

    it('B14 · lets users:create-admin set a password instead of inviting', function () {
        $this->artisan('users:create-admin')
            ->expectsQuestion('Email address', 'First@Example.com')
            ->expectsQuestion('Name', 'First Admin')
            ->expectsQuestion('Password', 'a-chosen-password')
            ->assertSuccessful();

        $admin = User::firstWhere('email', 'first@example.com');

        expect($admin?->assignedRole())->toBe(Role::Admin)
            ->and($admin?->email_verified_at)->not->toBeNull();
        Notification::assertNothingSent();

        $this->post(route('login.store'), ['email' => 'first@example.com', 'password' => 'a-chosen-password']);
        $this->assertAuthenticatedAs($admin);
    });

    it('B14 · takes the admin’s password as an option where no terminal can answer', function () {
        $this->artisan('users:create-admin', [
            '--email' => 'cloud@example.com',
            '--name' => 'Cloud Admin',
            '--password' => 'a-chosen-password',
        ])->assertSuccessful();

        expect(User::firstWhere('email', 'cloud@example.com')?->assignedRole())->toBe(Role::Admin);
        Notification::assertNothingSent();
    });
});
