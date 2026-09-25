<?php

declare(strict_types=1);

namespace App\Http\Visits\ShowVisits;

use Inertia\Inertia;
use Inertia\Response;

final class ShowVisitsController
{
    public function __invoke(ShowVisitsRequest $request, ShowVisitsService $show): Response
    {
        return Inertia::render('visits/index', $show($request->period()));
    }
}
