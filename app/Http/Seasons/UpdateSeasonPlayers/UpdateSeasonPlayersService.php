<?php

declare(strict_types=1);

namespace App\Http\Seasons\UpdateSeasonPlayers;

use App\Domain\History\Change;
use App\Domain\History\HistoryAction;
use App\Http\History\Ports\HistoryPort;
use App\Models\Player;
use App\Models\Season;
use Illuminate\Support\Facades\DB;

final class UpdateSeasonPlayersService
{
    public function __construct(private HistoryPort $history) {}

    /**
     * @param  list<int>  $playerIds
     */
    public function __invoke(Season $season, array $playerIds): void
    {
        DB::transaction(function () use ($season, $playerIds): void {
            /** @var list<string> $before */
            $before = $season->players()->pluck('players.name')->all();

            $season->players()->sync($playerIds);

            // LOG-01
            /** @var list<string> $after */
            $after = Player::query()->whereKey($playerIds)->pluck('name')->all();
            $this->history->record(HistoryAction::SeasonPlayersChanged, $season->name, Change::players($before, $after));
        });
    }
}
