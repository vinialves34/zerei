<?php

use App\Models\Game;
use App\Models\Playthrough;
use App\Models\User;
use App\Services\ReportService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->service = app(ReportService::class);
    $this->game = Game::factory()->completed()->for($this->user)->create();
});

test('completionsByYear counts the playthroughs of the user per year, newest first', function () {
    Playthrough::factory()->for($this->game)->count(2)->create(['completed_at' => '2024-04-01']);
    Playthrough::factory()->for($this->game)->create(['completed_at' => '2026-01-01']);
    Playthrough::factory()->create(['completed_at' => '2026-02-01']);

    expect($this->service->completionsByYear($this->user)->all())->toBe([2026 => 1, 2024 => 2]);
});

test('completionsByYear is empty when the user has no playthroughs', function () {
    expect($this->service->completionsByYear($this->user))->toBeEmpty();
});

test('monthlyCounts has twelve months and includes the first and last day of the year', function () {
    Playthrough::factory()->for($this->game)->count(2)->create(['completed_at' => '2026-01-01']);
    Playthrough::factory()->for($this->game)->create(['completed_at' => '2026-12-31']);
    Playthrough::factory()->for($this->game)->create(['completed_at' => '2025-12-31']);
    Playthrough::factory()->create(['completed_at' => '2026-06-10']);

    $counts = $this->service->monthlyCounts($this->user, 2026);

    expect($counts->keys()->all())->toBe(range(1, 12))
        ->and($counts->all())->toBe([1 => 2, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 1]);
});

test('timeline groups the playthroughs by month oldest first, with all twelve months', function () {
    Playthrough::factory()->for($this->game)->create(['completed_at' => '2026-03-20']);
    Playthrough::factory()->for($this->game)->create(['completed_at' => '2026-03-05']);
    Playthrough::factory()->for($this->game)->create(['completed_at' => '2025-03-05']);
    Playthrough::factory()->create(['completed_at' => '2026-03-01']);

    $timeline = $this->service->timeline($this->user, 2026);

    expect($timeline->keys()->all())->toBe(range(1, 12))
        ->and($timeline[3]->map->completed_at->map->toDateString()->all())->toBe(['2026-03-05', '2026-03-20'])
        ->and($timeline[4])->toBeEmpty();
});
