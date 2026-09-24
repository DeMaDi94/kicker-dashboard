<?php

declare(strict_types=1);

namespace App\Domain\Users;

/**
 * B13 — a user holds exactly one role. The stored value is the spatie role
 * name; the label is a translation key.
 */
enum Role: string
{
    case Admin = 'admin';
    case User = 'user';

    /**
     * @return list<Permission>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::Admin => Permission::cases(),
            self::User => [],
        };
    }
}
