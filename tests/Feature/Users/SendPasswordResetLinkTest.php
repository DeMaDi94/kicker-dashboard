<?php

declare(strict_types=1);

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

describe('B16 · an admin sends a password reset link', function () {
    beforeEach(fn () => mailOn());

    it('sends the reset mail', function () {
        Notification::fake();
        $user = member();

        $this->actingAs(admin())->post(route('users.password-reset-link', $user))
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        Notification::assertSentTo($user, ResetPassword::class);
    });

    it('is throttled like the forgotten-password form', function () {
        Notification::fake();
        $actor = admin();
        $user = member();

        $this->actingAs($actor)->post(route('users.password-reset-link', $user))->assertSessionHasNoErrors();
        $this->actingAs($actor)->post(route('users.password-reset-link', $user))
            ->assertInvalid(['user' => __('passwords.throttled')]);

        Notification::assertSentToTimes($user, ResetPassword::class, 1);
    });

    it('does not find an unknown user', function () {
        $this->actingAs(admin())->post(route('users.password-reset-link', 999))->assertNotFound();
    });

    it('B13 · refuses a user without the permission', function () {
        Notification::fake();
        $user = member();

        $this->actingAs(member())->post(route('users.password-reset-link', $user))->assertForbidden();

        Notification::assertNothingSent();
    });
});
