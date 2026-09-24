<?php

namespace Database\Factories;

use App\Models\Season;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Season>
 */
class SeasonFactory extends Factory
{
    /**
     * PEN-02 — the product owner's example scale.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = fake()->unique()->numberBetween(2000, 2098);

        return [
            'name' => sprintf('%d/%02d', $year, ($year + 1) % 100),
            'penalty_start_cents' => 450,
            'penalty_step_cents' => 50,
        ];
    }
}
