<?php

declare(strict_types=1);

namespace App\Http\Players\StorePlayer;

use App\Models\Player;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StorePlayerRequest extends FormRequest
{
    /**
     * PLY-01 — a name and the alias from the kicker Manager. D8 — the two
     * together are unique; a name alone may repeat.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Player::class)->where('alias', $this->string('alias')->toString()),
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
