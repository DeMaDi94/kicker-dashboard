---
name: laravel-permission-development
description: Roles and permissions through spatie/laravel-permission 8 as decision B13 sets them up — the Role and Permission enums in app/Domain/Users, route `can` middleware, $user->can() in services and policies, the auth.permissions prop the screens gate on, the migration that creates roles and permissions, assigning a role, querying users by role, and the permission cache. Load when adding or checking a permission, protecting a route or an action, showing UI only to some users, adding a role, or touching config/permission.php, the HasRoles trait or the roles migration. Not for sign-in, 2FA or passkeys (fortify-development).
license: MIT
metadata:
  source: "spatie/laravel-permission@8.3.0 resources/boost/skills/laravel-permission-development/SKILL.md"
  adapted: "Fitted to B13: permission checks only, never role names; TitleCase enums in app/Domain/Users; roles and permissions created by a migration, not a seeder; names only from a requirement or B13; imports instead of FQCNs (PermissionRegistrar resolved by class); Blade directives, direct user permissions, Super Admin, teams, wildcards and events dropped."
---

# Laravel Permission development

Decision B13 in `docs/DECISIONS.md` is the contract; read it (and B15, which guards the admin
role) before changing anything here.

## What B13 fixes

- **A user holds exactly one role**: `admin` or `user`. The roles are `App\Domain\Users\Role`.
- **Code checks permissions, never a role name.** The permissions are
  `App\Domain\Users\Permission`: `users.view`, `users.create`, `users.update`, `users.delete`
  (restoring a deleted user needs `users.delete`). `admin` holds all four; `user` none.
- **Roles and permissions are created by a migration**
  (`database/migrations/*_create_roles_and_permissions.php`), so every environment has them after
  `migrate` — no seeder needed in production.
- **The signed-in user's permissions are shared** with every page as `auth.permissions`
  (`HandleInertiaRequests`); screens gate on those.
- `HasRoles` is on `App\Models\User`, and `roles` / `permissions` are `#[Hidden]` so they never
  reach a page.

**Names are never invented.** A new role or permission comes from a requirement id or a decision
— cite it in the enum's docblock and the migration. If a screen seems to need a permission nobody
has specified, stop and ask.

## The enums

Enums live next to the rules that use them (`app/Domain/{Area}/`), with TitleCase cases. The
stored value is the spatie name and the wire format; once data exists it is not free to change.
The UI label is a translation key, produced by one function that returns literals
(`resources/js/features/users/role-label.ts`).

```php
namespace App\Domain\Users;

enum Permission: string
{
    case ViewUsers = 'users.view';
    case CreateUsers = 'users.create';
    case UpdateUsers = 'users.update';
    case DeleteUsers = 'users.delete';
}
```

`Role::permissions()` states which permissions each role holds; the migration freezes the same
set, and `tests/Feature/Users/RolesAndPermissionsTest.php` holds the two together.

`app/Domain` must not import spatie — the enums are plain PHP, and the framework code passes
their `->value` to the package.

## Checking

On the route — the house pattern (`routes/settings.php`):

```php
use App\Domain\Users\Permission;

Route::get('settings/users', ListUsersController::class)
    ->can(Permission::ViewUsers->value)
    ->name('users.index');
```

In a service or a policy, through the Gate:

```php
use App\Domain\Users\Permission;

if ($actor->can(Permission::DeleteUsers->value)) {
    // …
}
```

- Prefer `$user->can()` / `canAny()` (goes through the Gate, so policies and `Gate::before`
  apply) over `hasPermissionTo()`.
- In a FormRequest, `authorize()` returns `$this->user()?->can(Permission::…->value) ?? false`
  when the route does not already carry the check — not both.
- In React, gate what is shown, not what is allowed (the server still refuses):

```tsx
const { auth } = usePage().props;
const canCreate = auth.permissions.includes('users.create');
```

- **Never** `hasRole()`, `hasAnyRole()`, `role:` middleware, `role_or_permission:` middleware or a
  role name in a condition to decide what someone may do. A project that adds a role must not have
  to touch a single check.
- Querying **by role as data** is fine where a requirement is about the role itself — the role
  filter of the user list (B16), counting the active admins (B15, `ActiveAdmins`):
  `User::role(Role::Admin->value)`.

## Assigning a role

One role per user, so assign on create and sync on change (`StoreUserService`,
`UpdateUserService`):

```php
$user->assignRole($input->role->value);
$user->syncRoles($input->role->value);
```

Read it back as the enum with `$user->assignedRole()`. In tests,
`User::factory()->withRole(Role::Admin)->create()`. Direct permissions on a user are not used —
permissions go to roles.

A role change goes through `App\Domain\Users\AccountGuard` first (B15: no self-demotion, the last
active admin keeps the role).

## Adding a role or a permission

```text
- [ ] The requirement or decision that names it (never a made-up name)
- [ ] A case on App\Domain\Users\Role / Permission (TitleCase), and Role::permissions() updated
- [ ] A NEW migration that inserts it and its role_has_permissions rows — never edit the old one
- [ ] The migration flushes the cache: app(PermissionRegistrar::class)->forgetCachedPermissions()
- [ ] Route `can` middleware / $user->can() with the new permission
- [ ] The label in role-label.ts, the key in every lang/{locale}.json
- [ ] RolesAndPermissionsTest still passes; a feature test proves the refusal without it
```

The migration uses the query builder with frozen string values (see the existing one), not the
enums — enums may grow, a migration may not change after it has run.

```php
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

DB::table('permissions')->insert(['name' => 'reports.view', 'guard_name' => 'web', 'created_at' => $now, 'updated_at' => $now]);
// …role_has_permissions rows…
app(PermissionRegistrar::class)->forgetCachedPermissions();
```

(`reports.view` is a placeholder — the real name comes from the requirement.)

## Cache

Permissions are cached. Package methods (`givePermissionTo`, `syncPermissions`, …) flush it;
anything written with the query builder does not — call
`app(PermissionRegistrar::class)->forgetCachedPermissions()` after it, with `PermissionRegistrar`
imported.

## Off unless a decision turns them on

`config/permission.php` has `teams`, `enable_wildcard_permission` and `events_enabled` all
`false`. Multi-tenant teams, wildcard permissions, the attach/detach events and a Super Admin
`Gate::before` are each a `D*` decision first; for how they work then, read
`vendor/spatie/laravel-permission/src/` and its `README.md`.

## Debugging

- Who holds what: `php artisan tinker --execute 'dump(App\Models\User::firstWhere("email", "test@example.com")?->getAllPermissions()->pluck("name")->all());'`
- Roles and permissions per guard: `php artisan permission:show`. (Its `create-role` /
  `create-permission` / `assign-role` siblings are not how roles get made here — a migration is.)
- A stale answer after a direct DB change: `php artisan permission:cache-reset`.
- Which routes are guarded by what: `php artisan route:list --path=settings/users -v`.
