<?php

declare(strict_types=1);

namespace App\Http\Users\RestoreUser;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

final class RestoreUserController
{
    public function __invoke(User $user, RestoreUserService $restore): RedirectResponse
    {
        $restore($user);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('User restored.')]);

        return back();
    }
}
