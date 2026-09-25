<?php

declare(strict_types=1);

namespace App\Http\Users\EditMailSettings;

use App\Models\Setting;
use Inertia\Inertia;
use Inertia\Response;

final class EditMailSettingsController
{
    public function __invoke(): Response
    {
        return Inertia::render('settings/mail', [
            'mailEnabled' => Setting::mailEnabled(),
        ]);
    }
}
