<?php

declare(strict_types=1);

namespace App\Http\Users\DeleteUser;

use App\Domain\Users\AccountGuard;
use App\Http\Users\Shared\ActiveAdmins;
use App\Http\Users\Shared\GuardRefusalMessage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * B15 — a soft delete. The user's sessions end with it: the session guard and
 * the remember-me cookie resolve users without the deleted ones, so the next
 * request is a guest's.
 */
final class DeleteUserService
{
    /**
     * @throws ValidationException when AccountGuard refuses the deletion
     */
    public function __invoke(User $actor, User $user): void
    {
        DB::transaction(function () use ($actor, $user): void {
            GuardRefusalMessage::throwIf(
                AccountGuard::deletion($actor->id, $user->id, $user->assignedRole(), ActiveAdmins::count()),
                'user',
            );

            $user->delete();
        });
    }
}
