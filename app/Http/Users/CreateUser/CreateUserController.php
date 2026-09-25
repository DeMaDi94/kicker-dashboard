<?php

declare(strict_types=1);

namespace App\Http\Users\CreateUser;

use App\Domain\Users\Role;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

final class CreateUserController
{
    public function __invoke(): Response
    {
        return Inertia::render('settings/users/create', [
            'roles' => array_column(Role::cases(), 'value'),
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]);
    }
}
