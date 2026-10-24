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

test('counts the playthroughs of the current year per month', function () {
    $game = Game::factory()->completed()->for($this->user)->create();
    Playthrough::factory()->for($game)->count(2)->create(['completed_at' => '2026-01-31']);
    Playthrough::factory()->for($game)->create(['completed_at' => '2026-12-31']);
    Playthrough::factory()->for($game)->create(['completed_at' => '2025-12-31']);
    Playthrough::factory()->create(['completed_at' => '2026-01-10']);

    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertViewHas('monthlyCounts', fn ($counts) => $counts->keys()->all() === range(1, 12)
            && $counts->all() === [1 => 2, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 1]);
});

test('shows only the four most recent playthroughs of the user', function () {
    $game = Game::factory()->completed()->for($this->user)->create();

    foreach (range(1, 6) as $day) {
        Playthrough::factory()->for($game)->create(['completed_at' => "2026-01-0{$day}"]);
    }

    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertViewHas('recentPlaythroughs', fn ($recent) => $recent->count() === 4
            && $recent->first()->completed_at->toDateString() === '2026-01-06');
});

test('shows the platinum badge on recent platinum playthroughs only', function () {
    $game = Game::factory()->completed()->for($this->user)->create();
    Playthrough::factory()->for($game)->create(['completed_at' => '2026-02-10', 'completion_level' => 'story']);

    $this->actingAs($this->user)->get(route('dashboard'))->assertOk()->assertDontSee('data-platinum', false);

    Playthrough::factory()->for($game)->create(['completed_at' => '2026-03-10', 'completion_level' => 'platinum']);

    $this->actingAs($this->user)->get(route('dashboard'))->assertOk()->assertSee('data-platinum', false);
});
