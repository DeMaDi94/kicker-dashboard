<?php

declare(strict_types=1);

namespace App\Domain\Users;

/**
 * B15 — the guard rails around deleting users and changing their role.
 * `$activeAdmins` counts admins who are not soft-deleted, the target included.
 */
final class AccountGuard
{
    /**
     * An admin deleting a user from the user list.
     */
    public static function deletion(int $actorId, int $targetId, ?Role $targetRole, int $activeAdmins): ?GuardRefusal
    {
        if ($actorId === $targetId) {
            return GuardRefusal::OwnAccount;
        }

        return self::lastAdmin($targetRole, $activeAdmins);
    }

    /**
     * A user deleting their own account from the profile settings.
     */
    public static function selfDeletion(?Role $role, int $activeAdmins): ?GuardRefusal
    {
        return self::lastAdmin($role, $activeAdmins);
    }

    public static function roleChange(int $actorId, int $targetId, ?Role $from, Role $to, int $activeAdmins): ?GuardRefusal
    {
        if ($from !== Role::Admin || $to === Role::Admin) {
            return null;
        }

        if ($actorId === $targetId) {
            return GuardRefusal::OwnAdminRole;
        }

        return self::lastAdmin($from, $activeAdmins);
    }

    private static function lastAdmin(?Role $role, int $activeAdmins): ?GuardRefusal
    {
        return $role === Role::Admin && $activeAdmins <= 1 ? GuardRefusal::LastAdmin : null;
    }
}
