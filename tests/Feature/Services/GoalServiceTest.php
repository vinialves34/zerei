<?php

use App\Models\User;
use App\Models\YearlyGoal;
use App\Services\GoalService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->service = app(GoalService::class);
});

test('set creates the goal of the year', function () {
    $goal = $this->service->set($this->user, 2026, 20);

    expect($goal->user->is($this->user))->toBeTrue()
        ->and($goal->fresh())->year->toBe(2026)->target->toBe(20);
});

test('set changes the target instead of duplicating the goal of the same year', function () {
    $this->service->set($this->user, 2026, 20);
    $this->service->set($this->user, 2026, 30);

    expect($this->user->yearlyGoals()->count())->toBe(1)
        ->and($this->service->forYear($this->user, 2026)->target)->toBe(30);
});

test('each year keeps its own goal', function () {
    $this->service->set($this->user, 2025, 10);
    $this->service->set($this->user, 2026, 20);

    expect($this->service->forYear($this->user, 2025)->target)->toBe(10)
        ->and($this->service->forYear($this->user, 2026)->target)->toBe(20)
        ->and($this->service->forYear($this->user, 2024))->toBeNull();
});

test('forYear ignores the goals of other users', function () {
    YearlyGoal::factory()->create(['year' => 2026, 'target' => 99]);

    expect($this->service->forYear($this->user, 2026))->toBeNull();
});

test('remove deletes only the goal of the user and year', function () {
    $this->service->set($this->user, 2026, 20);
    $this->service->set($this->user, 2025, 10);
    $other = YearlyGoal::factory()->create(['year' => 2026]);

    $this->service->remove($this->user, 2026);

    expect($this->service->forYear($this->user, 2026))->toBeNull()
        ->and($this->service->forYear($this->user, 2025))->not->toBeNull()
        ->and($other->fresh())->not->toBeNull();
});

test('progress is null when the year has no goal', function () {
    expect($this->service->progress($this->user, 2026, 5))->toBeNull();
});

test('progress reports how far the user is from the goal', function () {
    $this->service->set($this->user, 2026, 20);

    expect($this->service->progress($this->user, 2026, 5))->toBe([
        'target' => 20,
        'completed' => 5,
        'remaining' => 15,
        'percent' => 25,
        'reached' => false,
    ]);
});

test('progress caps the percentage when the goal is exceeded', function () {
    $this->service->set($this->user, 2026, 4);

    expect($this->service->progress($this->user, 2026, 6))->toBe([
        'target' => 4,
        'completed' => 6,
        'remaining' => 0,
        'percent' => 100,
        'reached' => true,
    ]);
});

test('progress marks the goal as reached when the target is hit exactly', function () {
    $this->service->set($this->user, 2026, 3);

    expect($this->service->progress($this->user, 2026, 3))->toMatchArray(['percent' => 100, 'reached' => true, 'remaining' => 0]);
});
