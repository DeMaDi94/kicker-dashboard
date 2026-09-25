<?php

declare(strict_types=1);

namespace App\Http\News\UpdateNews;

use App\Models\NewsItem;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

final class UpdateNewsController
{
    public function __invoke(NewsItem $news, UpdateNewsRequest $request, UpdateNewsService $update): RedirectResponse
    {
        $update($news, $request->string('text')->toString());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('News saved.')]);

        return to_route('seasons.show', $news->season_id);
    }
}
