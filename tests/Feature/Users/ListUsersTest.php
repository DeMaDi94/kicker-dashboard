<?php

declare(strict_types=1);

use App\Domain\Users\Role;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

describe('B13 · who may see the user list', function () {
    it('sends a guest to the login', function () {
        $this->get(route('users.index'))->assertRedirect(route('login'));
    });

    it('refuses a user without the permission', function () {
        $this->actingAs(member())->get(route('users.index'))->assertForbidden();
    });
});

describe('B16 · the user list', function () {
    it('lists users with name, email, role and verification, sorted by name', function () {
        $admin = admin(['name' => 'Berta']);
        member(['name' => 'Anton', 'email_verified_at' => null]);

        $this->actingAs($admin)->get(route('users.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('settings/users/index')
                ->where('filters', ['search' => '', 'role' => '', 'status' => '', 'sort' => 'name'])
                ->where('roles', ['admin', 'user'])
                ->has('users', 2)
                ->where('users.0.name', 'Anton')
                ->where('users.0.role', 'user')
                ->where('users.0.emailVerifiedAt', null)
                ->where('users.0.deleted', false)
                ->where('users.1.name', 'Berta')
                ->where('users.1.role', 'admin')
                ->has('users.1.emailVerifiedAt')
                ->missing('users.0.password'));
    });

    it('pages by twenty', function () {
        $admin = admin();
        User::factory()->count(20)->withRole(Role::User)->create();

        $this->actingAs($admin)->get(route('users.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('users', 20)
                ->where('pagination', ['page' => 1, 'lastPage' => 2, 'total' => 21]));

        $this->actingAs($admin)->get(route('users.index', ['page' => 2]))
            ->assertInertia(fn (AssertableInertia $page) => $page->has('users', 1));
    });

    it('searches name and email', function () {
        $admin = admin(['name' => 'Admin', 'email' => 'admin@example.com']);
        member(['name' => 'Clara Klein', 'email' => 'c@example.com']);
        member(['name' => 'Dora', 'email' => 'klein.d@example.com']);

        $this->actingAs($admin)->get(route('users.index', ['search' => 'klein']))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('users', 2)
                ->where('users.0.name', 'Clara Klein')
                ->where('users.1.name', 'Dora'));
    });

    it('filters by role', function () {
        $admin = admin();
        member();

        $this->actingAs($admin)->get(route('users.index', ['role' => 'admin']))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('users', 1)
                ->where('users.0.id', $admin->id));
    });

    it('shows deleted users only under the deleted filter', function () {
        $admin = admin();
        $gone = member();
        $gone->delete();

        $this->actingAs($admin)->get(route('users.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page->has('users', 1));

        $this->actingAs($admin)->get(route('users.index', ['status' => 'deleted']))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('users', 1)
                ->where('users.0.id', $gone->id)
                ->where('users.0.deleted', true));
    });

    it('sorts by every column, both ways', function (string $sort, array $expected) {
        $admin = admin(['name' => 'Anna', 'email' => 'z@example.com', 'email_verified_at' => '2026-01-02']);
        member(['name' => 'Bert', 'email' => 'y@example.com', 'email_verified_at' => '2026-01-03']);
        member(['name' => 'Cleo', 'email' => 'x@example.com', 'email_verified_at' => '2026-01-01']);

        $this->actingAs($admin)->get(route('users.index', ['sort' => $sort]))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('users', fn ($users) => collect($users)->pluck('name')->all() === $expected));
    })->with([
        ['name', ['Anna', 'Bert', 'Cleo']],
        ['-name', ['Cleo', 'Bert', 'Anna']],
        ['email', ['Cleo', 'Bert', 'Anna']],
        ['-email', ['Anna', 'Bert', 'Cleo']],
        ['role', ['Anna', 'Bert', 'Cleo']],
        ['-role', ['Bert', 'Cleo', 'Anna']],
        ['verified', ['Cleo', 'Anna', 'Bert']],
        ['-verified', ['Bert', 'Anna', 'Cleo']],
    ]);

    it('rejects an unknown sort, role or status', function (array $query) {
        $this->actingAs(admin())->get(route('users.index', $query))->assertInvalid(array_keys($query));
    })->with([[['sort' => 'password']], [['role' => 'owner']], [['status' => 'all']]]);
});
