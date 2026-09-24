<?php

declare(strict_types=1);

use App\Domain\Users\AccountGuard;
use App\Domain\Users\GuardRefusal;
use App\Domain\Users\Role;

describe('B15 · deleting a user from the list', function () {
    it('refuses an admin deleting their own account', function () {
        expect(AccountGuard::deletion(1, 1, Role::Admin, 3))->toBe(GuardRefusal::OwnAccount);
    });

    it('refuses deleting the last active admin', function () {
        expect(AccountGuard::deletion(1, 2, Role::Admin, 1))->toBe(GuardRefusal::LastAdmin);
    });

    it('allows deleting an admin while another remains', function () {
        expect(AccountGuard::deletion(1, 2, Role::Admin, 2))->toBeNull();
    });

    it('allows deleting a user, and one without a role', function (?Role $role) {
        expect(AccountGuard::deletion(1, 2, $role, 1))->toBeNull();
    })->with([Role::User, null]);
});

describe('B15 · deleting one’s own account from the profile', function () {
    it('refuses the last active admin', function () {
        expect(AccountGuard::selfDeletion(Role::Admin, 1))->toBe(GuardRefusal::LastAdmin);
    });

    it('allows an admin while another remains, and any user', function (?Role $role, int $admins) {
        expect(AccountGuard::selfDeletion($role, $admins))->toBeNull();
    })->with([[Role::Admin, 2], [Role::User, 1], [null, 0]]);
});

describe('B15 · changing a role', function () {
    it('refuses an admin removing the admin role from themselves', function () {
        expect(AccountGuard::roleChange(1, 1, Role::Admin, Role::User, 3))->toBe(GuardRefusal::OwnAdminRole);
    });

    it('refuses demoting the last active admin', function () {
        expect(AccountGuard::roleChange(1, 2, Role::Admin, Role::User, 1))->toBe(GuardRefusal::LastAdmin);
    });

    it('allows demoting an admin while another remains', function () {
        expect(AccountGuard::roleChange(1, 2, Role::Admin, Role::User, 2))->toBeNull();
    });

    it('allows every change that does not take the admin role away', function (?Role $from, Role $to) {
        expect(AccountGuard::roleChange(1, 1, $from, $to, 1))->toBeNull();
    })->with([[Role::Admin, Role::Admin], [Role::User, Role::Admin], [Role::User, Role::User], [null, Role::User]]);
});
