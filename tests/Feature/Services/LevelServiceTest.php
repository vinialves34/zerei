<?php

use App\Enums\CompletionLevel;
use App\Enums\LevelTitle;
use App\Models\Game;
use App\Models\Playthrough;
use App\Models\User;
use App\Services\LevelService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->service = app(LevelService::class);
    $this->game = Game::factory()->completed()->for($this->user)->create();
});

test('each completion level has its points', function () {
    expect(CompletionLevel::Story->points())->toBe(50)
        ->and(CompletionLevel::Full->points())->toBe(100)
        ->and(CompletionLevel::Platinum->points())->toBe(200);
});

test('the curve starts each level at 50 × N × (N − 1) points', function () {
    expect($this->service->pointsForLevel(1))->toBe(0)
        ->and($this->service->pointsForLevel(2))->toBe(100)
        ->and($this->service->pointsForLevel(5))->toBe(1000)
        ->and($this->service->pointsForLevel(10))->toBe(4500)
        ->and($this->service->pointsForLevel(20))->toBe(19000);
});

test('levelForPoints changes level exactly at the threshold', function (int $points, int $level) {
    expect($this->service->levelForPoints($points))->toBe($level);
})->with([
    [0, 1],
    [99, 1],
    [100, 2],
    [299, 2],
    [300, 3],
    [999, 4],
    [1000, 5],
    [4499, 9],
    [4500, 10],
    [19000, 20],
    [1_000_000, 141],
]);

test('titles follow the level ranges', function (int $level, LevelTitle $title) {
    expect(LevelTitle::forLevel($level))->toBe($title);
})->with([
    [1, LevelTitle::Rookie],
    [4, LevelTitle::Rookie],
    [5, LevelTitle::Player],
    [9, LevelTitle::Player],
    [10, LevelTitle::Veteran],
    [19, LevelTitle::Veteran],
    [20, LevelTitle::Legend],
    [60, LevelTitle::Legend],
]);

test('points sum the playthroughs by completion level', function () {
    Playthrough::factory()->for($this->game)->create(['completion_level' => CompletionLevel::Story]);
    Playthrough::factory()->for($this->game)->count(2)->create(['completion_level' => CompletionLevel::Full]);
    Playthrough::factory()->for($this->game)->create(['completion_level' => CompletionLevel::Platinum]);

    expect($this->service->points($this->user))->toBe(450);
});

test('points are zero without playthroughs', function () {
    expect($this->service->points($this->user))->toBe(0);
});

test('points ignore the playthroughs of other users', function () {
    Playthrough::factory()->create(['completion_level' => CompletionLevel::Platinum]);

    expect($this->service->points($this->user))->toBe(0);
});

test('points follow edits and removals of playthroughs', function () {
    $playthrough = Playthrough::factory()->for($this->game)->create(['completion_level' => CompletionLevel::Story]);

    $playthrough->update(['completion_level' => CompletionLevel::Platinum]);
    expect($this->service->points($this->user))->toBe(200);

    $playthrough->delete();
    expect($this->service->points($this->user))->toBe(0);
});

test('points of a year include the first and last day and leave other years out', function () {
    Playthrough::factory()->for($this->game)->create(['completed_at' => '2026-01-01', 'completion_level' => CompletionLevel::Story]);
    Playthrough::factory()->for($this->game)->create(['completed_at' => '2026-12-31', 'completion_level' => CompletionLevel::Full]);
    Playthrough::factory()->for($this->game)->create(['completed_at' => '2025-12-31', 'completion_level' => CompletionLevel::Platinum]);

    expect($this->service->points($this->user, 2026))->toBe(150)
        ->and($this->service->points($this->user))->toBe(350);
});

test('progress starts at level 1 for a user without playthroughs', function () {
    expect($this->service->progress($this->user))->toBe([
        'points' => 0,
        'level' => 1,
        'title' => LevelTitle::Rookie,
        'pointsIntoLevel' => 0,
        'pointsForNextLevel' => 100,
        'remaining' => 100,
        'percent' => 0,
    ]);
});

test('progress reports the position inside the current level', function () {
    Playthrough::factory()->for($this->game)->count(3)->create(['completion_level' => CompletionLevel::Full]);
    Playthrough::factory()->for($this->game)->create(['completion_level' => CompletionLevel::Story]);

    expect($this->service->progress($this->user))->toBe([
        'points' => 350,
        'level' => 3,
        'title' => LevelTitle::Rookie,
        'pointsIntoLevel' => 50,
        'pointsForNextLevel' => 300,
        'remaining' => 250,
        'percent' => 16,
    ]);
});
