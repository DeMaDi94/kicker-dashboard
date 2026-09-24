<?php

declare(strict_types=1);

namespace App\Http\Seasons\StoreSeason;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

final class StoreSeasonController
{
    public function __invoke(StoreSeasonRequest $request, StoreSeasonService $store): RedirectResponse
    {
        $season = $store($request->toInput());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Season created.')]);

        return to_route('seasons.show', $season);
    }
}
