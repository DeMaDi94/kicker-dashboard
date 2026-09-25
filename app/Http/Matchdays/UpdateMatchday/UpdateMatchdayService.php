<?php

declare(strict_types=1);

namespace App\Http\Matchdays\UpdateMatchday;

use App\Domain\History\Change;
use App\Domain\History\HistoryAction;
use App\Http\History\Ports\HistoryPort;
use App\Models\Score;
use App\Models\Season;
use Illuminate\Support\Facades\DB;

final class UpdateMatchdayService
{
    public function __construct(private HistoryPort $history) {}

    /**
     * MD-01 / MD-04 — enter or change the points of a matchday; places,
     * penalties and the table are derived on read and so follow at once.
     *
     * @param  array<int, int>  $points  player id => points
     */
    public function __invoke(Season $season, int $matchday, array $points): void
    {
        DB::transaction(function () use ($season, $matchday, $points): void {
            /** @var array<int, int> $before */
            $before = $season->scores()->where('matchday', $matchday)->pluck('points', 'player_id')->all();

            foreach ($points as $playerId => $value) {
                Score::updateOrCreate(
                    ['season_id' => $season->id, 'matchday' => $matchday, 'player_id' => $playerId],
                    ['points' => $value],
                );
            }

            // LOG-01, D17 — one save is one entry, with every point it changed.
            /** @var array<int, string> $names */
            $names = $season->players()->pluck('players.name', 'players.id')->all();
            $this->history->record(HistoryAction::PointsSaved, $season->name, Change::points($names, $before, $points), $matchday);
        });
    }
}
