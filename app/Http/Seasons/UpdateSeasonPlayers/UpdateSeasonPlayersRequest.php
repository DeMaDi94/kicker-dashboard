<?php

declare(strict_types=1);

namespace App\Http\Seasons\UpdateSeasonPlayers;

use App\Http\Seasons\Shared\SeasonLock;
use App\Models\Player;
use App\Models\Season;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class UpdateSeasonPlayersRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'player_ids' => ['present', 'array'],
            'player_ids.*' => ['integer', 'distinct', Rule::exists(Player::class, 'id')],
        ];
    }

    /**
     * @return list<callable(Validator): void>
     */
    public function after(SeasonLock $lock): array
    {
        return [
            function (Validator $validator) use ($lock): void {
                $season = $this->route('season');

                // SEA-03 — fixed from the first entered point on.
                if ($season instanceof Season && $lock($season)) {
                    $validator->errors()->add('player_ids', __('Points have been entered for this season, so its players can no longer change.'));
                }
            },
        ];
    }

    /**
     * @return list<int>
     */
    public function playerIds(): array
    {
        /** @var list<int|string> $ids */
        $ids = $this->input('player_ids', []);

        return array_map(intval(...), $ids);
    }
}
