<?php

declare(strict_types=1);

namespace App\Http\Visits\ShowVisits;

use App\Domain\Visits\VisitPeriod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class ShowVisitsRequest extends FormRequest
{
    /**
     * D14 — nothing to refuse: a `days` value that is not a period opens the
     * preselected one.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [];
    }

    public function period(): VisitPeriod
    {
        return VisitPeriod::fromDays($this->filled('days') ? $this->integer('days') : null);
    }
}
