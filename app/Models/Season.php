<?php

namespace App\Models;

use App\Domain\Penalties\PenaltyScale;
use Database\Factories\SeasonFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * SEA-01 — a season: its name and its penalty scale (PEN-01), in cents.
 *
 * @property int $id
 * @property string $name
 * @property int $penalty_start_cents
 * @property int $penalty_step_cents
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'penalty_start_cents', 'penalty_step_cents'])]
class Season extends Model
{
    /** @use HasFactory<SeasonFactory> */
    use HasFactory;

    /**
     * @return BelongsToMany<Player, $this>
     */
    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class);
    }

    /**
     * @return HasMany<Score, $this>
     */
    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }

    public function penaltyScale(): PenaltyScale
    {
        return new PenaltyScale($this->penalty_start_cents, $this->penalty_step_cents);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'penalty_start_cents' => 'integer',
            'penalty_step_cents' => 'integer',
        ];
    }
}
