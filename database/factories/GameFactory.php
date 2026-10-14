<?php

namespace Database\Factories;

use App\Enums\GameStatus;
use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Game>
 */
class GameFactory extends Factory
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
            'title' => fake()->unique()->words(fake()->numberBetween(1, 4), true),
            'status' => GameStatus::Backlog,
        ];
    }

    public function playing(): static
    {
        return $this->state(['status' => GameStatus::Playing]);
    }

    public function completed(): static
    {
        return $this->state(['status' => GameStatus::Completed]);
    }

    public function dropped(): static
    {
        return $this->state(['status' => GameStatus::Dropped]);
    }
}
