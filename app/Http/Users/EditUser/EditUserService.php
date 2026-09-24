<?php

declare(strict_types=1);

namespace App\Http\Users\EditUser;

use App\Domain\Users\AccountGuard;
use App\Domain\Users\Role;
use App\Http\Users\Shared\ActiveAdmins;
use App\Http\Users\Shared\GuardRefusalMessage;
use App\Http\Users\Shared\UserRow;
use App\Models\User;

/**
 * The edit screen's props, including why a delete or a demotion would be
 * refused (B15), so the screen can say so before the admin tries.
 *
 * @phpstan-import-type UserRowShape from UserRow
 */
final class EditUserService
{
    /**
     * @return array{user: UserRowShape, deleteRefusal: string|null, demoteRefusal: string|null}
     */
    public function __invoke(User $actor, User $user): array
    {
        $activeAdmins = ActiveAdmins::count();
        $delete = AccountGuard::deletion($actor->id, $user->id, $user->assignedRole(), $activeAdmins);
        $demote = AccountGuard::roleChange($actor->id, $user->id, $user->assignedRole(), Role::User, $activeAdmins);

        return [
            'user' => UserRow::from($user),
            'deleteRefusal' => $delete === null ? null : GuardRefusalMessage::for($delete),
            'demoteRefusal' => $demote === null ? null : GuardRefusalMessage::for($demote),
        ];
    }
}
