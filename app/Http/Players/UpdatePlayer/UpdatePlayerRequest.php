<?php

declare(strict_types=1);

namespace App\Http\Players\UpdatePlayer;

use App\Models\Player;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdatePlayerRequest extends FormRequest
{
    /**
     * PLY-02 — the name and the alias, as when the player was created
     * (PLY-01). D8 — the two together stay unique among the other players.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $player = $this->route('player');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Player::class)
                    ->where('alias', $this->string('alias')->toString())
                    ->ignore($player instanceof Player ? $player->id : null),
            ],
            'alias' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['name.unique' => __('A player with this name and alias already exists.')];
    }
}
