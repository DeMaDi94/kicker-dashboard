<?php

declare(strict_types=1);

namespace App\Http\Seasons\UpdatePenaltyScale;

use App\Models\Season;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

final class UpdatePenaltyScaleController
{
    public function __invoke(Season $season, UpdatePenaltyScaleRequest $request, UpdatePenaltyScaleService $update): RedirectResponse
    {
        $update($season, $request->penaltyStartCents(), $request->penaltyStepCents());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Penalty scale saved.')]);

        return to_route('seasons.show', $season);
    }
}
