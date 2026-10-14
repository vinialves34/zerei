<?php

namespace Database\Factories;

use App\Enums\CompletionLevel;
use App\Enums\Platform;
use App\Models\Game;
use App\Models\Playthrough;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Playthrough>
 */
class PlaythroughFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'game_id' => Game::factory()->completed(),
            'completed_at' => fake()->dateTimeBetween('-2 years', 'now'),
            'platform' => fake()->randomElement(Platform::cases()),
            'rating' => fake()->numberBetween(1, 5),
            'completion_level' => fake()->randomElement(CompletionLevel::cases()),
        ];
    }
}
