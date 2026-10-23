<?php

use App\Models\Game;
use App\Models\Playthrough;
use App\Models\User;
use App\Services\DashboardService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->service = app(DashboardService::class);
});

test('summary gathers the indicators of the user for the year', function () {
    $completed = Game::factory()->completed()->for($this->user)->create();
    Playthrough::factory()->for($completed)->count(2)->create(['completed_at' => '2026-02-10']);
    Playthrough::factory()->for($completed)->create(['completed_at' => '2025-12-31']);
    Game::factory()->for($this->user)->count(3)->create();
    Game::factory()->playing()->for($this->user)->create(['title' => 'Elden Ring']);
    Game::factory()->create();

    $summary = $this->service->summary($this->user, 2026);

    expect($summary['year'])->toBe(2026)
        ->and($summary['completedThisYear'])->toBe(2)
        ->and($summary['monthlyCounts'][2])->toBe(2)
        ->and($summary['backlogCount'])->toBe(3)
        ->and($summary['playingGames']->pluck('title')->all())->toBe(['Elden Ring']);
});

test('summary limits the recent playthroughs to the four newest', function () {
    $game = Game::factory()->completed()->for($this->user)->create();

    foreach (range(1, 6) as $day) {
        Playthrough::factory()->for($game)->create(['completed_at' => "2026-01-0{$day}"]);
    }

    $recent = $this->service->summary($this->user, 2026)['recentPlaythroughs'];

    expect($recent)->toHaveCount(4)
        ->and($recent->first()->completed_at->toDateString())->toBe('2026-01-06');
});
