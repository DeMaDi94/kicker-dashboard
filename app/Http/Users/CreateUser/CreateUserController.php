<?php

declare(strict_types=1);

namespace App\Http\Users\CreateUser;

use App\Domain\Users\Role;
use Inertia\Inertia;
use Inertia\Response;

final class CreateUserController
{
    public function __invoke(): Response
    {
        return Inertia::render('settings/users/create', [
            'roles' => array_column(Role::cases(), 'value'),
        ]);
    }
}
