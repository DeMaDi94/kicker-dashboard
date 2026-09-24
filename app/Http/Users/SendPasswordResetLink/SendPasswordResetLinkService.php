<?php

declare(strict_types=1);

namespace App\Http\Users\SendPasswordResetLink;

use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

/**
 * B16 — the same reset mail the user would get from „Forgot password“, and
 * throttled the same way.
 */
final class SendPasswordResetLinkService
{
    /**
     * @throws ValidationException when the broker does not send the link
     */
    public function __invoke(User $user): void
    {
        $status = Password::broker()->sendResetLink(['email' => $user->email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages(['user' => __($status)]);
        }
    }
}
