<?php

declare(strict_types=1);

namespace App\Http\Users\StoreUser;

use App\Domain\Users\Role;

final readonly class StoreUserInput
{
    public function __construct(
        public string $name,
        public string $email,
        public Role $role,
    ) {}
}
