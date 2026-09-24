<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Laravel\Passkeys\Passkey as BasePasskey;

/**
 * B15 — a soft-deleted user's passkeys are not found, so signing in with one
 * fails as an unrecognised passkey does. Restoring the user brings them back.
 */
class Passkey extends BasePasskey
{
    protected static function booted(): void
    {
        static::addGlobalScope('active-user', fn (Builder $passkeys) => $passkeys->whereHas('user'));
    }
}
