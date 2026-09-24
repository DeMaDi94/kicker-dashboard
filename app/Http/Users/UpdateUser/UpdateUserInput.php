<?php

declare(strict_types=1);

namespace App\Http\Users\UpdateUser;

use App\Domain\Users\Role;

final readonly class UpdateUserInput
{
    public function __construct(
        public string $name,
        public Role $role,
    ) {}
}
