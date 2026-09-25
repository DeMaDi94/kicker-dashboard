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
 *
 * D15 — or the admin sets the password: no mail, and the address counts as
 * verified, since the admin vouches for it (as with users:set-password, D13).
 */
final class StoreUserService
{
    public function __invoke(StoreUserInput $input): User
    {
        $user = DB::transaction(function () use ($input): User {
            $user = (new User)->forceFill([
                'name' => $input->name,
                'email' => $input->email,
                'password' => $input->password ?? Str::password(64),
                'email_verified_at' => $input->password === null ? null : now(),
            ]);
            $user->save();

            $user->assignRole($input->role->value);

            return $user;
        });

        if ($input->password === null) {
            $user->notify(new UserInvitation(Password::broker()->createToken($user)));
        }

        return $user;
    }
}
