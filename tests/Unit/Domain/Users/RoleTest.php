<?php

declare(strict_types=1);

use App\Domain\Users\Permission;
use App\Domain\Users\Role;

describe('B13 · roles', function () {
    it('gives the admin every permission and the user none', function () {
        expect(Role::Admin->permissions())->toBe(Permission::cases())
            ->and(Role::User->permissions())->toBe([]);
    });
});
