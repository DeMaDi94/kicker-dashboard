<?php

declare(strict_types=1);

namespace App\Http\Seasons\ShowSeason;

use App\Models\Season;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * ACC-01 — readable without signing in; D1 — also the page `/` shows.
 */
final class ShowSeasonController
{
    public function __invoke(Request $request, ShowSeasonService $show, ?Season $season = null): Response
    {
        return Inertia::render('seasons/show', $show($season, $request->user()));
    }
}
