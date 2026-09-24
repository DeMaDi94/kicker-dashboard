<?php

declare(strict_types=1);

namespace App\Http\Statistics\ShowPlayer;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class ShowPlayerRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return ['season' => ['nullable', 'integer']];
    }

    public function seasonId(): ?int
    {
        return $this->filled('season') ? $this->integer('season') : null;
    }
}
