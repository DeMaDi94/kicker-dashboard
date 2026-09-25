<?php

declare(strict_types=1);

namespace App\Http\Users\StoreUser;

use App\Domain\Users\Role;

final readonly class StoreUserInput
{
    /**
     * @param  string|null  $password  set by the admin (D15); null sends the invitation (B14)
     */
    public function __construct(
        public string $name,
        public string $email,
        public Role $role,
        #[\SensitiveParameter] public ?string $password = null,
    ) {}
}
