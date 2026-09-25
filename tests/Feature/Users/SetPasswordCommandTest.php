<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;

describe('D13 · a password set from the command line', function () {
    it('sets the password and marks the address verified', function () {
        $user = member(['email' => 'invited@example.com', 'email_verified_at' => null]);

        $this->artisan('users:set-password', ['--email' => 'Invited@Example.com', '--password' => 'a-new-password'])
            ->assertSuccessful();

        $user->refresh();

        expect(Hash::check('a-new-password', $user->password))->toBeTrue()
            ->and($user->email_verified_at)->not->toBeNull();
    });

    it('asks for what the options leave out', function () {
        $user = member(['email' => 'invited@example.com']);

        $this->artisan('users:set-password')
            ->expectsQuestion('Email address', 'invited@example.com')
            ->expectsQuestion('New password', 'a-new-password')
            ->assertSuccessful();

        expect(Hash::check('a-new-password', $user->refresh()->password))->toBeTrue();
    });

    it('refuses an address nobody has', function () {
        $this->artisan('users:set-password', ['--email' => 'nobody@example.com', '--password' => 'a-new-password'])
            ->assertFailed();

        expect(User::count())->toBe(0);
    });
});
