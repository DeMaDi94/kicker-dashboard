<?php

declare(strict_types=1);

use App\Domain\Users\Role;
use App\Http\Users\StoreUser\UserInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

describe('B14 · the first admin comes from the command line', function () {
    it('creates an admin and sends the invitation', function () {
        Notification::fake();

        $this->artisan('users:create-admin')
            ->expectsQuestion('Email address', 'First@Example.com')
            ->expectsQuestion('Name', 'First Admin')
            ->assertSuccessful();

        $admin = User::firstWhere('email', 'first@example.com');

        expect($admin?->assignedRole())->toBe(Role::Admin);
        Notification::assertSentTo($admin, UserInvitation::class);
    });

    it('takes name and address as options where no terminal can answer a prompt', function () {
        Notification::fake();

        $this->artisan('users:create-admin', ['--email' => 'Cloud@Example.com', '--name' => 'Cloud Admin'])
            ->assertSuccessful();

        $admin = User::firstWhere('email', 'cloud@example.com');

        expect($admin?->name)->toBe('Cloud Admin')
            ->and($admin?->assignedRole())->toBe(Role::Admin);
        Notification::assertSentTo($admin, UserInvitation::class);
    });

    it('refuses an address that is taken', function () {
        Notification::fake();
        member(['email' => 'taken@example.com']);

        $this->artisan('users:create-admin')
            ->expectsQuestion('Email address', 'taken@example.com')
            ->expectsQuestion('Name', 'Someone')
            ->assertFailed();

        Notification::assertNothingSent();
    });
});
