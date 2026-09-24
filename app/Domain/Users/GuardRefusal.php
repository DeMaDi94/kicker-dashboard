<?php

declare(strict_types=1);

namespace App\Domain\Users;

/**
 * Why AccountGuard refused a change (B15).
 */
enum GuardRefusal
{
    case OwnAccount;
    case OwnAdminRole;
    case LastAdmin;
}
