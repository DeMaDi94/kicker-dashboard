<?php

declare(strict_types=1);

namespace App\Http\Statistics\ShowRecords;

use Inertia\Inertia;
use Inertia\Response;

final class ShowRecordsController
{
    public function __invoke(ShowRecordsRequest $request, ShowRecordsService $show): Response
    {
        return Inertia::render('statistics/records', $show($request->seasonId()));
    }
}
