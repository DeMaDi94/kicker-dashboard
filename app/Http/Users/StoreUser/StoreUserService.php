<?php

declare(strict_types=1);

namespace App\Http\Users\StoreUser;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * B14 — an admin creates the account; the user sets the password through the
 * invitation. Until then the password is a random one nobody knows.
 */
final class StoreUserService
{
    public function __invoke(StoreUserInput $input): User
    {
        $user = DB::transaction(function () use ($input): User {
            $user = User::create([
                'name' => $input->name,
                'email' => $input->email,
                'password' => Str::password(64),
            ]);

            $user->assignRole($input->role->value);

            return $user;
        });

        $user->notify(new UserInvitation(Password::broker()->createToken($user)));

        return $user;
    }
}
