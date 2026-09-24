<?php

declare(strict_types=1);

namespace App\Http\Statistics\ComparePlayers;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class ComparePlayersRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return ['a' => ['nullable', 'integer'], 'b' => ['nullable', 'integer']];
    }

    public function a(): ?int
    {
        return $this->filled('a') ? $this->integer('a') : null;
    }

    public function b(): ?int
    {
        return $this->filled('b') ? $this->integer('b') : null;
    }
}
