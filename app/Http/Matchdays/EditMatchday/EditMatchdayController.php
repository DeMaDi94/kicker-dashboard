<?php

declare(strict_types=1);

namespace App\Http\Matchdays\EditMatchday;

use App\Domain\Seasons\SeasonLength;
use App\Models\Season;
use Inertia\Inertia;
use Inertia\Response;

final class EditMatchdayController
{
    public function __invoke(Season $season, int $matchday, EditMatchdayService $players): Response
    {
        return Inertia::render('matchdays/edit', [
            'season' => ['id' => $season->id, 'name' => $season->name],
            'matchday' => $matchday,
            'matchdays' => SeasonLength::matchdays(),
            'players' => $players($season, $matchday),
        ]);
    }
}
