<?php

use App\Models\Game;
use App\Models\Playthrough;
use App\Models\User;

beforeEach(function () {
    $this->travelTo('2026-06-15');
    $this->user = User::factory()->create();
});

test('redirects guests to the login screen', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('summarizes the current year, the backlog and what is being played', function () {
    $completed = Game::factory()->completed()->for($this->user)->create();
    Playthrough::factory()->for($completed)->count(2)->create(['completed_at' => '2026-02-10']);
    Playthrough::factory()->for($completed)->create(['completed_at' => '2025-12-31']);
    Game::factory()->for($this->user)->count(3)->create();
    Game::factory()->playing()->for($this->user)->create(['title' => 'Elden Ring']);
    Game::factory()->create();

    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertViewHas('year', 2026)
        ->assertViewHas('completedThisYear', 2)
        ->assertViewHas('backlogCount', 3)
        ->assertSee('Elden Ring');
});

test('shows only the five most recent playthroughs of the user', function () {
    $game = Game::factory()->completed()->for($this->user)->create();

    foreach (range(1, 6) as $day) {
        Playthrough::factory()->for($game)->create(['completed_at' => "2026-01-0{$day}"]);
    }

    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertViewHas('recentPlaythroughs', fn ($recent) => $recent->count() === 5
            && $recent->first()->completed_at->toDateString() === '2026-01-06');
});
