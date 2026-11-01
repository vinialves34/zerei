<?php

use App\Enums\Achievement;
use App\Enums\CompletionLevel;
use App\Enums\Platform;
use App\Models\Game;
use App\Models\Playthrough;
use App\Models\User;
use App\Services\AchievementService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->service = app(AchievementService::class);
    $this->playthrough = fn (array $attributes = [], ?User $user = null) => Playthrough::factory()
        ->for(Game::factory()->completed()->for($user ?? $this->user))
        ->create($attributes + ['completion_level' => CompletionLevel::Story, 'platform' => Platform::Pc]);
    $this->unlocked = fn () => $this->service->forUser($this->user)
        ->filter(fn (array $item) => $item['unlocked'])
        ->map(fn (array $item) => $item['achievement'])
        ->values()
        ->all();
});

test('lists every achievement, all locked for a new user', function () {
    $achievements = $this->service->forUser($this->user);

    expect($achievements)->toHaveCount(count(Achievement::cases()))
        ->and(($this->unlocked)())->toBeEmpty();
});

test('a platinum playthrough unlocks the first platinum', function () {
    ($this->playthrough)(['completion_level' => CompletionLevel::Full]);
    expect(($this->unlocked)())->not->toContain(Achievement::FirstPlatinum);

    ($this->playthrough)(['completion_level' => CompletionLevel::Platinum]);
    expect(($this->unlocked)())->toContain(Achievement::FirstPlatinum);
});

test('ten playthroughs in the same year unlock the achievement, spread over years they do not', function () {
    foreach (range(1, 9) as $month) {
        ($this->playthrough)(['completed_at' => "2026-0{$month}-01"]);
    }
    ($this->playthrough)(['completed_at' => '2025-12-31']);
    expect(($this->unlocked)())->not->toContain(Achievement::TenInAYear);

    ($this->playthrough)(['completed_at' => '2026-12-31']);
    expect(($this->unlocked)())->toContain(Achievement::TenInAYear);
});

test('three playthroughs within seven days unlock the achievement', function () {
    ($this->playthrough)(['completed_at' => '2026-03-01']);
    ($this->playthrough)(['completed_at' => '2026-03-04']);
    ($this->playthrough)(['completed_at' => '2026-03-08']);
    expect(($this->unlocked)())->not->toContain(Achievement::ThreeInAWeek);

    ($this->playthrough)(['completed_at' => '2026-03-07']);
    expect(($this->unlocked)())->toContain(Achievement::ThreeInAWeek);
});

test('three playthroughs on the same day count as a week', function () {
    ($this->playthrough)(['completed_at' => '2026-03-01']);
    ($this->playthrough)(['completed_at' => '2026-03-01']);
    ($this->playthrough)(['completed_at' => '2026-03-01']);

    expect(($this->unlocked)())->toContain(Achievement::ThreeInAWeek);
});

test('five different platforms unlock the achievement', function () {
    foreach ([Platform::Pc, Platform::PlayStation5, Platform::NintendoSwitch, Platform::XboxSeries, Platform::Pc] as $platform) {
        ($this->playthrough)(['platform' => $platform]);
    }
    expect(($this->unlocked)())->not->toContain(Achievement::FivePlatforms);

    ($this->playthrough)(['platform' => Platform::Wii]);
    expect(($this->unlocked)())->toContain(Achievement::FivePlatforms);
});

test('the playthroughs of other users do not count', function () {
    ($this->playthrough)(['completion_level' => CompletionLevel::Platinum], User::factory()->create());

    expect(($this->unlocked)())->toBeEmpty();
});

test('removing the playthrough locks the achievement again', function () {
    $platinum = ($this->playthrough)(['completion_level' => CompletionLevel::Platinum]);
    expect(($this->unlocked)())->toContain(Achievement::FirstPlatinum);

    $platinum->delete();

    expect(($this->unlocked)())->toBeEmpty();
});
