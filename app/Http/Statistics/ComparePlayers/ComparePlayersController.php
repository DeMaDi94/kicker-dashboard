<?php

declare(strict_types=1);

namespace App\Http\Statistics\ComparePlayers;

use App\Models\Season;
use Inertia\Inertia;
use Inertia\Response;

final class ComparePlayersController
{
    public function __invoke(Season $season, ComparePlayersRequest $request, ComparePlayersService $compare): Response
    {
        return Inertia::render('statistics/compare', $compare($season, $request->a(), $request->b()));
    }
}
