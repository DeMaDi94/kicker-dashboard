<?php

declare(strict_types=1);

namespace App\Http\Users\UpdateMailSettings;

use App\Models\Setting;

/**
 * D16 — switches outgoing mail on or off for the whole installation.
 */
final class UpdateMailSettingsService
{
    public function __invoke(bool $mailEnabled): void
    {
        Setting::current()->fill(['mail_enabled' => $mailEnabled])->save();
    }
}
