<?php

declare(strict_types=1);

namespace App\Http\Users\UpdateMailSettings;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

final class UpdateMailSettingsController
{
    public function __invoke(UpdateMailSettingsRequest $request, UpdateMailSettingsService $update): RedirectResponse
    {
        $update($request->boolean('mail_enabled'));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Email settings saved.')]);

        return to_route('mail-settings.edit');
    }
}
