<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\YearlyGoal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<YearlyGoal>
 */
class YearlyGoalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'year' => now()->year,
            'target' => fake()->numberBetween(5, 50),
        ];
    }
}
