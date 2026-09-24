<?php

declare(strict_types=1);

namespace App\Http\Matchdays\UpdateMatchday;

use App\Models\Score;
use App\Models\Season;
use Illuminate\Support\Facades\DB;

final class UpdateMatchdayService
{
    /**
     * MD-01 / MD-04 — enter or change the points of a matchday; places,
     * penalties and the table are derived on read and so follow at once.
     *
     * @param  array<int, int>  $points  player id => points
     */
    public function __invoke(Season $season, int $matchday, array $points): void
    {
        DB::transaction(function () use ($season, $matchday, $points): void {
            foreach ($points as $playerId => $value) {
                Score::updateOrCreate(
                    ['season_id' => $season->id, 'matchday' => $matchday, 'player_id' => $playerId],
                    ['points' => $value],
                );
            }
        });
    }
}
