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
    public function __invoke(Request $request, ShowSeasonService $show, MatchdayPreview $preview, ?Season $season = null): Response
    {
        $view = $show($season, $request->user());

        // MD-07 — the preview tags go into the HTML itself: a messenger runs no script.
        return Inertia::render('seasons/show', $view)
            ->withViewData('preview', $preview($view, $request->integer('matchday')));
    }
}
