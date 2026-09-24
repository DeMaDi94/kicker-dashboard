<?php

declare(strict_types=1);

namespace App\Http\Users\UpdateUser;

use App\Domain\Users\AccountGuard;
use App\Http\Users\Shared\ActiveAdmins;
use App\Http\Users\Shared\GuardRefusalMessage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class UpdateUserService
{
    /**
     * @throws ValidationException when AccountGuard refuses the role change (B15)
     */
    public function __invoke(User $actor, User $user, UpdateUserInput $input): void
    {
        DB::transaction(function () use ($actor, $user, $input): void {
            GuardRefusalMessage::throwIf(
                AccountGuard::roleChange($actor->id, $user->id, $user->assignedRole(), $input->role, ActiveAdmins::count()),
                'role',
            );

            $user->update(['name' => $input->name]);
            $user->syncRoles($input->role->value);
        });
    }
}
