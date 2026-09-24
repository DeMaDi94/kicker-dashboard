<?php

declare(strict_types=1);

namespace App\Http\Users\RestoreUser;

use App\Models\User;

/**
 * B15 — a deleted user comes back with their role and their address, which
 * nobody else can have taken in the meantime.
 */
final class RestoreUserService
{
    public function __invoke(User $user): void
    {
        $user->restore();
    }
}
