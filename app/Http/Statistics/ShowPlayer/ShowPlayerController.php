<?php

declare(strict_types=1);

namespace App\Http\Statistics\ShowPlayer;

use App\Models\Player;
use Inertia\Inertia;
use Inertia\Response;

/**
 * STAT-01 — readable without signing in.
 */
final class ShowPlayerController
{
    public function __invoke(Player $player, ShowPlayerRequest $request, ShowPlayerService $show): Response
    {
        return Inertia::render('statistics/player', $show($player, $request->seasonId()));
    }
}
