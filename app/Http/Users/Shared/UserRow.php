<?php

declare(strict_types=1);

namespace App\Http\Users\Shared;

use App\Models\User;

/**
 * One user as the user screens receive it — never the raw model.
 *
 * @phpstan-type UserRowShape array{id: int, name: string, email: string, role: string|null, emailVerifiedAt: string|null, deleted: bool}
 */
final class UserRow
{
    /**
     * @return UserRowShape
     */
    public static function from(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->assignedRole()?->value,
            'emailVerifiedAt' => $user->email_verified_at?->toIso8601String(),
            'deleted' => $user->trashed(),
        ];
    }
}
