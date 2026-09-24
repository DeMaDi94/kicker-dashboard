<?php

declare(strict_types=1);

namespace App\Http\Users\UpdateUser;

use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

final class UpdateUserController
{
    public function __invoke(UpdateUserRequest $request, #[CurrentUser] User $actor, User $user, UpdateUserService $update): RedirectResponse
    {
        $update($actor, $user, $request->toInput());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('User updated.')]);

        return to_route('users.edit', $user);
    }
}
