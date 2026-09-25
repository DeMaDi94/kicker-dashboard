<?php

declare(strict_types=1);

namespace App\Http\News\StoreNews;

use App\Models\Season;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

final class StoreNewsController
{
    public function __invoke(Season $season, StoreNewsRequest $request, StoreNewsService $store): RedirectResponse
    {
        $store($season, $request->user(), $request->string('text')->toString());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('News saved.')]);

        return to_route('seasons.show', $season);
    }
}
