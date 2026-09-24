<?php

declare(strict_types=1);

namespace App\Http\Users\Shared;

use App\Domain\Users\GuardRefusal;
use Illuminate\Validation\ValidationException;

final class GuardRefusalMessage
{
    public static function for(GuardRefusal $refusal): string
    {
        return match ($refusal) {
            GuardRefusal::OwnAccount => __('You cannot delete your own account here.'),
            GuardRefusal::OwnAdminRole => __('You cannot remove the admin role from yourself.'),
            GuardRefusal::LastAdmin => __('The last admin cannot be deleted or lose the admin role.'),
        };
    }

    /**
     * @throws ValidationException
     */
    public static function throwIf(?GuardRefusal $refusal, string $field): void
    {
        if ($refusal !== null) {
            throw ValidationException::withMessages([$field => self::for($refusal)]);
        }
    }
}
