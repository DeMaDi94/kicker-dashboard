<?php

declare(strict_types=1);

namespace App\Http\News\ListNews;

use App\Domain\News\NewsAccess;
use App\Domain\Users\Permission;
use App\Models\NewsItem;
use App\Models\Season;
use App\Models\User;

/**
 * NEWS-02 — a season's news, newest first, each with its date and its
 * author's name; NEWS-03 — and whether the viewer may change it.
 *
 * @phpstan-type NewsLine array{id: int, text: string, authorName: string, postedAt: string, edited: bool, mayChange: bool}
 */
final class ListNewsService
{
    /**
     * @return list<NewsLine>
     */
    public function __invoke(Season $season, ?User $viewer): array
    {
        $mayManage = $viewer?->can(Permission::ManageNews->value) ?? false;

        return array_values(NewsItem::query()
            ->whereBelongsTo($season)
            ->with('author:id,name,deleted_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (NewsItem $news): array => [
                'id' => $news->id,
                'text' => $news->text,
                'authorName' => $news->author->name,
                'postedAt' => $news->created_at?->toIso8601String() ?? '',
                // D17 — an edited post shows „bearbeitet“ beside its date.
                'edited' => $news->edited_at !== null,
                'mayChange' => $viewer !== null && NewsAccess::mayChange($news->user_id, $viewer->id, $mayManage),
            ])
            ->all());
    }
}
