<?php

declare(strict_types=1);

namespace App\Http\Seasons\UpdateSeasonSettlement;

use App\Models\Season;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

final class UpdateSeasonSettlementController
{
    public function __invoke(Season $season, UpdateSeasonSettlementRequest $request, UpdateSeasonSettlementService $update): RedirectResponse
    {
        $update($season, $request->settlementMatchday());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Interim settlement saved.')]);

        return to_route('seasons.show', $season);
    }
}
