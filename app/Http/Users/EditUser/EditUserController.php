<?php

declare(strict_types=1);

namespace App\Http\Users\EditUser;

use App\Domain\Users\Role;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Inertia\Inertia;
use Inertia\Response;

final class EditUserController
{
    public function __invoke(#[CurrentUser] User $actor, User $user, EditUserService $edit): Response
    {
        return Inertia::render('settings/users/edit', [
            ...$edit($actor, $user),
            'roles' => array_column(Role::cases(), 'value'),
        ]);
    }
}
