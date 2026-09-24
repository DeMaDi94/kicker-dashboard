<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * MD-01 — one player's points on one matchday of a season.
 *
 * @property int $id
 * @property int $season_id
 * @property int $player_id
 * @property int $matchday
 * @property int $points
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['season_id', 'player_id', 'matchday', 'points'])]
class Score extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'matchday' => 'integer',
            'points' => 'integer',
        ];
    }
}
