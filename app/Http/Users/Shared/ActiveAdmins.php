<?php

declare(strict_types=1);

namespace App\Http\Users\Shared;

use App\Domain\Users\Role;
use App\Models\User;

/**
 * The number of admins who are not soft-deleted — what AccountGuard counts
 * against (B15). Inside a transaction the rows are locked, so two admins
 * demoting each other at once cannot both pass.
 */
final class ActiveAdmins
{
    public static function count(): int
    {
        // Counted in PHP: PostgreSQL refuses FOR UPDATE on an aggregate.
        return count(User::role(Role::Admin->value)->lockForUpdate()->pluck('users.id')->all());
    }
}
