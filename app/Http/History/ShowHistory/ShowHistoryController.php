<?php

declare(strict_types=1);

namespace App\Http\History\ShowHistory;

use Inertia\Inertia;
use Inertia\Response;

final class ShowHistoryController
{
    public function __invoke(ShowHistoryRequest $request, ShowHistoryService $show): Response
    {
        return Inertia::render('history/index', $show($request->page()));
    }
}
