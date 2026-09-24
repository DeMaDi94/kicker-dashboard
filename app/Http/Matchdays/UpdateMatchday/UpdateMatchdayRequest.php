<?php

declare(strict_types=1);

namespace App\Http\Matchdays\UpdateMatchday;

use App\Models\Season;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateMatchdayRequest extends FormRequest
{
    /**
     * MD-01 — points for every player of the season, and for them only;
     * whole numbers, negative allowed.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $ids = $this->participantIds();

        $rules = ['points' => ['required', 'array:'.implode(',', $ids)]];

        foreach ($ids as $id) {
            $rules["points.{$id}"] = ['required', 'integer'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return array_combine(
            array_map(fn (int $id): string => "points.{$id}", $this->participantIds()),
            array_fill(0, count($this->participantIds()), __('Points')),
        );
    }

    /**
     * @return array<int, int> player id => points
     */
    public function points(): array
    {
        $points = [];

        foreach ($this->participantIds() as $id) {
            $points[$id] = $this->integer("points.{$id}");
        }

        return $points;
    }

    /**
     * @return list<int>
     */
    private function participantIds(): array
    {
        $season = $this->route('season');

        if (! $season instanceof Season) {
            return [];
        }

        return array_values($season->players()->pluck('players.id')->map(fn (mixed $id): int => (int) $id)->all());
    }
}
