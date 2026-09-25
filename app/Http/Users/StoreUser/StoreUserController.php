<?php

declare(strict_types=1);

namespace App\Http\Users\StoreUser;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

final class StoreUserController
{
    public function __invoke(StoreUserRequest $request, StoreUserService $store): RedirectResponse
    {
        $input = $request->toInput();
        $store($input);

        Inertia::flash('toast', ['type' => 'success', 'message' => $input->password === null
            ? __('User created. An invitation is on its way.')
            : __('User created. They can sign in with the password you set.')]);

        return to_route('users.index');
    }
}
