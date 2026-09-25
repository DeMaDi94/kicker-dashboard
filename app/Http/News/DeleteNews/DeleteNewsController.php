<?php

declare(strict_types=1);

namespace App\Http\News\DeleteNews;

use App\Models\NewsItem;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

final class DeleteNewsController
{
    public function __invoke(NewsItem $news, DeleteNewsRequest $request, DeleteNewsService $delete): RedirectResponse
    {
        $delete($news);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('News deleted.')]);

        return to_route('seasons.show', $news->season_id);
    }
}
