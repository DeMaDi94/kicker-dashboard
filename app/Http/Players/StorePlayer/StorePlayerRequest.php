<?php

declare(strict_types=1);

namespace App\Http\Players\StorePlayer;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class StorePlayerRequest extends FormRequest
{
    /**
     * PLY-01 — a name and the alias from the kicker Manager.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'alias' => ['required', 'string', 'max:255'],
        ];
    }
}
