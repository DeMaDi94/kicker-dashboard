<?php

declare(strict_types=1);

namespace App\Http\News\Ports;

use App\Http\News\ListNews\ListNewsService;
use App\Models\Season;
use App\Models\User;

/**
 * What the News area lets others ask: a season's news for the season view
 * (NEWS-02), with what the viewer may change (NEWS-03).
 *
 * @phpstan-import-type NewsLine from ListNewsService
 */
final class SeasonNewsPort
{
    public function __construct(private ListNewsService $list) {}

    /**
     * @return list<NewsLine>
     */
    public function forSeason(Season $season, ?User $viewer): array
    {
        return ($this->list)($season, $viewer);
    }
}
