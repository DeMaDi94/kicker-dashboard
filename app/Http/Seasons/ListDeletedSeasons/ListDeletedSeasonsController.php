<?php

declare(strict_types=1);

namespace App\Http\Seasons\ListDeletedSeasons;

use Inertia\Inertia;
use Inertia\Response;

final class ListDeletedSeasonsController
{
    public function __invoke(ListDeletedSeasonsService $list): Response
    {
        return Inertia::render('seasons/deleted', ['seasons' => $list()]);
    }
}
