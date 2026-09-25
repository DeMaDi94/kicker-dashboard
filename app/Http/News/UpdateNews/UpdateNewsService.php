<?php

declare(strict_types=1);

namespace App\Http\News\UpdateNews;

use App\Domain\History\Change;
use App\Domain\History\HistoryAction;
use App\Http\History\Ports\HistoryPort;
use App\Models\NewsItem;
use Illuminate\Support\Facades\DB;

final class UpdateNewsService
{
    public function __construct(private HistoryPort $history) {}

    /**
     * NEWS-03 — the text changes; D17 — the post then shows „bearbeitet“.
     * Saving the same text changes nothing.
     */
    public function __invoke(NewsItem $news, string $text): void
    {
        if ($news->text === $text) {
            return;
        }

        DB::transaction(function () use ($news, $text): void {
            $before = $news->text;
            $news->update(['text' => $text, 'edited_at' => now()]);

            // LOG-01
            $this->history->record(HistoryAction::NewsUpdated, $news->season->name, Change::between(['text' => $before], ['text' => $text]));
        });
    }
}
