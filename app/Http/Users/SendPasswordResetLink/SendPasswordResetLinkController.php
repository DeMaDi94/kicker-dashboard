<?php

declare(strict_types=1);

namespace App\Http\Users\SendPasswordResetLink;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

final class SendPasswordResetLinkController
{
    public function __invoke(User $user, SendPasswordResetLinkService $send): RedirectResponse
    {
        $send($user);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Password reset link sent.')]);

        return back();
    }
}
