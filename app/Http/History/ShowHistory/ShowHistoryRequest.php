<?php

declare(strict_types=1);

namespace App\Http\History\ShowHistory;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class ShowHistoryRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return ['page' => ['nullable', 'integer', 'min:1']];
    }

    public function page(): int
    {
        return max(1, $this->integer('page', 1));
    }
}
