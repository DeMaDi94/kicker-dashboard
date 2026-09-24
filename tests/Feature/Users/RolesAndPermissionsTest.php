<?php

declare(strict_types=1);

use App\Domain\Users\Permission;
use App\Domain\Users\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Role as RoleModel;

describe('B13 · roles and permissions', function () {
    it('are created by the migrations exactly as the domain defines them', function () {
        expect(RoleModel::query()->orderBy('name')->pluck('name')->all())
            ->toBe(['admin', 'user']);

        foreach (Role::cases() as $role) {
            expect(RoleModel::findByName($role->value)->permissions->pluck('name')->sort()->values()->all())
                ->toBe(collect($role->permissions())->map(fn (Permission $permission) => $permission->value)->sort()->values()->all());
        }
    });

    it('shares the signed-in user’s permissions with every page', function () {
        $admin = User::factory()->withRole(Role::Admin)->create();

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('auth.permissions', fn ($permissions) => collect($permissions)->sort()->values()->all()
                    === collect(Permission::cases())->map(fn (Permission $permission) => $permission->value)->sort()->values()->all())
                ->missing('auth.user.roles'));

        $this->actingAs(User::factory()->withRole(Role::User)->create())->get(route('dashboard'))
            ->assertInertia(fn (AssertableInertia $page) => $page->where('auth.permissions', []));
    });

    it('seeds an admin and a user, and can seed again', function () {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        expect(User::count())->toBe(2)
            ->and(User::firstWhere('email', 'test@example.com')?->assignedRole())->toBe(Role::Admin)
            ->and(User::firstWhere('email', 'user@example.com')?->assignedRole())->toBe(Role::User);
    });
});
