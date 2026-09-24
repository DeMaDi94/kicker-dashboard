<?php

declare(strict_types=1);

namespace App\Spec;

enum RequirementStatus: string
{
    /** Not started. */
    case Planned = 'planned';

    /** Partially built — some of the rule exists, the requirement is not met yet. */
    case InProgress = 'in-progress';

    /** Fully implemented and covered by at least one test citing its ID. */
    case Done = 'done';

    /** Deliberately implemented differently from how the requirement is written. Needs a reason. */
    case Changed = 'changed';

    /** Deliberately not built. Needs a reason. */
    case WontDo = 'wont-do';

    /** A status that has to justify itself, or a reviewer reads it as a missing feature. */
    public function requiresReason(): bool
    {
        return $this === self::Changed || $this === self::WontDo;
    }

    /** Only these claim the requirement is met, so only these demand a test. */
    public function requiresTest(): bool
    {
        return $this === self::Done;
    }
}
