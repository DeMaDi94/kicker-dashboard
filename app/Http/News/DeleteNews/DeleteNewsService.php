<?php

declare(strict_types=1);

namespace App\Http\News\DeleteNews;

use App\Domain\History\Change;
use App\Domain\History\ChangedField;
use App\Domain\History\HistoryAction;
use App\Http\History\Ports\HistoryPort;
use App\Models\NewsItem;
use Illuminate\Support\Facades\DB;

final class DeleteNewsService
{
    public function __construct(private HistoryPort $history) {}

    /**
     * NEWS-03 — a deleted post is gone; LOG-01 — the history keeps its text.
     */
    public function __invoke(NewsItem $news): void
    {
        DB::transaction(function () use ($news): void {
            $news->delete();

            $this->history->record(HistoryAction::NewsDeleted, $news->season->name, [new Change(ChangedField::Text, $news->text, null)]);
        });
    }
}
