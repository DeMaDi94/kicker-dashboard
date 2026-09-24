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
        // D8 — names are unique; tests name their own seasons 20xx, so these never collide.
        $year = fake()->unique()->numberBetween(1900, 1998);

        return [
            'name' => sprintf('%d/%02d', $year, ($year + 1) % 100),
            'penalty_start_cents' => 450,
            'penalty_step_cents' => 50,
        ];
    }
}
