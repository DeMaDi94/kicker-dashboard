<?php

declare(strict_types=1);

namespace App\Http\Matchdays\UpdateMatchday;

use App\Models\Season;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

final class UpdateMatchdayController
{
    public function __invoke(Season $season, int $matchday, UpdateMatchdayRequest $request, UpdateMatchdayService $update): RedirectResponse
    {
        $update($season, $matchday, $request->points());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Points saved.')]);

        return to_route('seasons.show', ['season' => $season, 'matchday' => $matchday]);
    }
}
