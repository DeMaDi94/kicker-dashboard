<?php

declare(strict_types=1);

namespace App\Http\News\StoreNews;

use App\Domain\History\Change;
use App\Domain\History\ChangedField;
use App\Domain\History\HistoryAction;
use App\Http\History\Ports\HistoryPort;
use App\Models\NewsItem;
use App\Models\Season;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class StoreNewsService
{
    public function __construct(private HistoryPort $history) {}

    /**
     * NEWS-01 — any signed-in user writes a news post for a season.
     */
    public function __invoke(Season $season, User $author, string $text): NewsItem
    {
        return DB::transaction(function () use ($season, $author, $text): NewsItem {
            $news = NewsItem::create(['season_id' => $season->id, 'user_id' => $author->id, 'text' => $text]);

            // LOG-01
            $this->history->record(HistoryAction::NewsCreated, $season->name, [new Change(ChangedField::Text, null, $text)]);

            return $news;
        });
    }
}
