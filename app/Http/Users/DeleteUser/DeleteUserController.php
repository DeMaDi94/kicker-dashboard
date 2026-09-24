<?php

declare(strict_types=1);

namespace App\Http\Users\DeleteUser;

use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

final class DeleteUserController
{
    public function __invoke(#[CurrentUser] User $actor, User $user, DeleteUserService $delete): RedirectResponse
    {
        $delete($actor, $user);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('User deleted.')]);

        return to_route('users.index');
    }
}
