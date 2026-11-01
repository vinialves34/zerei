<?php

use App\Enums\CompletionLevel;
use App\Enums\LevelTitle;
use App\Models\Game;
use App\Models\Playthrough;
use App\Models\User;
use App\Models\YearlyGoal;
use App\Services\LevelService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->service = app(LevelService::class);
    $this->game = Game::factory()->completed()->for($this->user)->create();
    $this->playthrough = fn (array $attributes = []) => Playthrough::factory()
        ->for(Game::factory()->completed()->for($this->user))
        ->create($attributes);
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
    ($this->playthrough)(['completion_level' => CompletionLevel::Story]);
    ($this->playthrough)(['completion_level' => CompletionLevel::Full]);
    ($this->playthrough)(['completion_level' => CompletionLevel::Full]);
    ($this->playthrough)(['completion_level' => CompletionLevel::Platinum]);

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
    ($this->playthrough)(['completed_at' => '2026-01-01', 'completion_level' => CompletionLevel::Story]);
    ($this->playthrough)(['completed_at' => '2026-12-31', 'completion_level' => CompletionLevel::Full]);
    ($this->playthrough)(['completed_at' => '2025-12-31', 'completion_level' => CompletionLevel::Platinum]);

    expect($this->service->points($this->user, 2026))->toBe(150)
        ->and($this->service->points($this->user))->toBe(350);
});

test('replays of the same game earn half of the points', function () {
    Playthrough::factory()->for($this->game)->create(['completed_at' => '2025-03-01', 'completion_level' => CompletionLevel::Platinum]);
    Playthrough::factory()->for($this->game)->create(['completed_at' => '2026-03-01', 'completion_level' => CompletionLevel::Full]);
    Playthrough::factory()->for($this->game)->create(['completed_at' => '2026-04-01', 'completion_level' => CompletionLevel::Story]);

    expect($this->service->points($this->user))->toBe(200 + 50 + 25)
        ->and($this->service->points($this->user, 2026))->toBe(75);
});

test('the earliest playthrough is the one that earns full points', function () {
    $later = Playthrough::factory()->for($this->game)->create(['completed_at' => '2026-05-01', 'completion_level' => CompletionLevel::Full]);
    Playthrough::factory()->for($this->game)->create(['completed_at' => '2026-01-01', 'completion_level' => CompletionLevel::Full]);

    expect($this->service->points($this->user))->toBe(150);

    $later->delete();

    expect($this->service->points($this->user))->toBe(100);
});

test('reaching the yearly goal gives a bonus to that year', function () {
    YearlyGoal::factory()->for($this->user)->create(['year' => 2026, 'target' => 2]);
    YearlyGoal::factory()->for($this->user)->create(['year' => 2025, 'target' => 5]);
    ($this->playthrough)(['completed_at' => '2026-01-10', 'completion_level' => CompletionLevel::Story]);
    ($this->playthrough)(['completed_at' => '2026-02-10', 'completion_level' => CompletionLevel::Story]);
    ($this->playthrough)(['completed_at' => '2025-02-10', 'completion_level' => CompletionLevel::Story]);

    expect($this->service->points($this->user, 2026))->toBe(100 + LevelService::GOAL_BONUS)
        ->and($this->service->points($this->user, 2025))->toBe(50)
        ->and($this->service->points($this->user))->toBe(150 + LevelService::GOAL_BONUS);
});

test('a goal that is not reached or was removed gives no bonus', function () {
    YearlyGoal::factory()->for($this->user)->create(['year' => 2026, 'target' => 2]);
    ($this->playthrough)(['completed_at' => '2026-01-10', 'completion_level' => CompletionLevel::Story]);

    expect($this->service->points($this->user))->toBe(50);

    ($this->playthrough)(['completed_at' => '2026-02-10', 'completion_level' => CompletionLevel::Story]);
    expect($this->service->points($this->user))->toBe(100 + LevelService::GOAL_BONUS);

    $this->user->yearlyGoals()->delete();
    expect($this->service->points($this->user))->toBe(100);
});

test('the goals of other users give no bonus', function () {
    YearlyGoal::factory()->create(['year' => 2026, 'target' => 1]);
    ($this->playthrough)(['completed_at' => '2026-01-10', 'completion_level' => CompletionLevel::Story]);

    expect($this->service->points($this->user))->toBe(50);
});

test('claimLevelUp only records the level the first time', function () {
    expect($this->user->last_seen_level)->toBeNull()
        ->and($this->service->claimLevelUp($this->user, 3))->toBeNull()
        ->and($this->user->fresh()->last_seen_level)->toBe(3);
});

test('claimLevelUp announces a higher level once', function () {
    $this->user->forceFill(['last_seen_level' => 2])->save();

    expect($this->service->claimLevelUp($this->user, 4))->toBe(4)
        ->and($this->service->claimLevelUp($this->user->fresh(), 4))->toBeNull();
});

test('claimLevelUp does not announce a lower level but remembers it', function () {
    $this->user->forceFill(['last_seen_level' => 5])->save();

    expect($this->service->claimLevelUp($this->user, 3))->toBeNull()
        ->and($this->user->fresh()->last_seen_level)->toBe(3)
        ->and($this->service->claimLevelUp($this->user->fresh(), 4))->toBe(4);
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
    foreach ([CompletionLevel::Full, CompletionLevel::Full, CompletionLevel::Full, CompletionLevel::Story] as $level) {
        ($this->playthrough)(['completion_level' => $level]);
    }

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
