<?php

namespace App\Models;

use Database\Factories\PlayerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * PLY-01 — a player of the league, with the alias from the kicker Manager.
 * ACC-04 — not a user account.
 *
 * @property int $id
 * @property string $name
 * @property string $alias
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'alias'])]
class Player extends Model
{
    /** @use HasFactory<PlayerFactory> */
    use HasFactory;
}
