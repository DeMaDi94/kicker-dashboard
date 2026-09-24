<?php

declare(strict_types=1);

namespace App\Http\Seasons\UpdateSeasonSettlement;

use App\Domain\Seasons\SeasonLength;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateSeasonSettlementRequest extends FormRequest
{
    /**
     * PEN-04 — any matchday but the last, or none.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'settlement_matchday' => ['nullable', 'integer', Rule::in(SeasonLength::settlementMatchdays())],
        ];
    }

    public function settlementMatchday(): ?int
    {
        return $this->filled('settlement_matchday') ? $this->integer('settlement_matchday') : null;
    }
}
