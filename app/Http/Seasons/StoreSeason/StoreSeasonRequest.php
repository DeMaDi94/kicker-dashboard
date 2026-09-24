<?php

declare(strict_types=1);

namespace App\Http\Seasons\StoreSeason;

use App\Domain\Seasons\SeasonLength;
use App\Models\Player;
use App\Models\Season;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreSeasonRequest extends FormRequest
{
    public const string MAX_EUROS = '42949672.95';

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
            // D8 — no two seasons share a name.
            // D8 — unique among the seasons not deleted; a deleted season frees its name.
            'name' => ['required', 'string', 'max:255', Rule::unique(Season::class)->withoutTrashed()],
            'penalty_start' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:'.self::MAX_EUROS],
            'penalty_step' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:'.self::MAX_EUROS],
            // PEN-04 — optional; any matchday but the last.
            'settlement_matchday' => ['nullable', 'integer', Rule::in(SeasonLength::settlementMatchdays())],
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
            settlementMatchday: $this->filled('settlement_matchday') ? $this->integer('settlement_matchday') : null,
        );
    }
}
