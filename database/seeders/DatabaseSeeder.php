<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\Playthrough;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Game::factory()
            ->count(12)
            ->completed()
            ->for($user)
            ->has(Playthrough::factory(), 'playthroughs')
            ->create();

        Game::factory()->count(5)->for($user)->create();
        Game::factory()->count(2)->playing()->for($user)->create();
        Game::factory()->dropped()->for($user)->create();
    }
}
