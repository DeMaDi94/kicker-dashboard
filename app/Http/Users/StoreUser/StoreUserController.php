<?php

declare(strict_types=1);

namespace App\Http\Users\StoreUser;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

final class StoreUserController
{
    public function __invoke(StoreUserRequest $request, StoreUserService $store): RedirectResponse
    {
        $store($request->toInput());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('User created. An invitation is on its way.')]);

        return to_route('users.index');
    }
}
