<?php

declare(strict_types=1);

namespace App\Http\Seasons\UpdatePenaltyScale;

use App\Http\Seasons\StoreSeason\StoreSeasonRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class UpdatePenaltyScaleRequest extends FormRequest
{
    /**
     * SEA-05 — the same amounts SEA-01 takes when the season is created: euros
     * to the cent, none below 0 € (PEN-01), up to the cents column's bound.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'penalty_start' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:'.StoreSeasonRequest::MAX_EUROS],
            'penalty_step' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:'.StoreSeasonRequest::MAX_EUROS],
        ];
    }

    public function penaltyStartCents(): int
    {
        return (int) round($this->float('penalty_start') * 100);
    }

    public function penaltyStepCents(): int
    {
        return (int) round($this->float('penalty_step') * 100);
    }
}
