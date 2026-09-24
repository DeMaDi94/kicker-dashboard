<?php

declare(strict_types=1);

namespace App\Http\Seasons\StoreSeason;

use App\Models\Player;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreSeasonRequest extends FormRequest
{
    private const string MAX_EUROS = '42949672.95';

    /**
     * SEA-01 — the name and the penalty scale, in euros to the cent; PEN-01 —
     * no amount below 0 €. SEA-02 — the season's players. The upper bound is
     * the cents column's (unsigned 32 bit), not a rule of the league.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'penalty_start' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:'.self::MAX_EUROS],
            'penalty_step' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:'.self::MAX_EUROS],
            'player_ids' => ['present', 'array'],
            'player_ids.*' => ['integer', 'distinct', Rule::exists(Player::class, 'id')],
        ];
    }

    public function toInput(): StoreSeasonInput
    {
        /** @var list<int|string> $playerIds */
        $playerIds = $this->input('player_ids', []);

        return new StoreSeasonInput(
            name: $this->string('name')->toString(),
            penaltyStartCents: (int) round($this->float('penalty_start') * 100),
            penaltyStepCents: (int) round($this->float('penalty_step') * 100),
            playerIds: array_map(intval(...), $playerIds),
        );
    }
}
