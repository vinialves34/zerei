<?php

use App\Enums\CompletionLevel;
use App\Enums\Platform;
use App\Models\Game;
use App\Models\Playthrough;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

test('a playthrough casts its attributes', function () {
    $playthrough = Playthrough::factory()->create()->fresh();

    expect($playthrough->completed_at)->toBeInstanceOf(CarbonInterface::class)
        ->and($playthrough->platform)->toBeInstanceOf(Platform::class)
        ->and($playthrough->completion_level)->toBeInstanceOf(CompletionLevel::class)
        ->and($playthrough->rating)->toBeInt()->toBeBetween(1, 5);
});

test('a playthrough belongs to a game', function () {
    $playthrough = Playthrough::factory()->create();

    expect($playthrough->game)->toBeInstanceOf(Game::class);
});

test('every platform and completion level has a label', function () {
    foreach ([...Platform::cases(), ...CompletionLevel::cases()] as $case) {
        expect($case->label())->toBeString()->not->toBeEmpty();
    }
});

test('the platform list has the Nintendo 64 and Super Nintendo options and no Mobile', function () {
    $labels = collect(Platform::cases())->map->label();

    expect($labels)->toContain('Nintendo 64', 'Super Nintendo')
        ->not->toContain('Mobile')
        ->and(Platform::from('n64'))->toBe(Platform::Nintendo64)
        ->and(Platform::from('snes'))->toBe(Platform::SuperNintendo)
        ->and(Platform::tryFrom('mobile'))->toBeNull();
});

test('the mobile platform migration moves its playthroughs to other', function () {
    $mobile = Playthrough::factory()->create();
    $switch = Playthrough::factory()->create(['platform' => Platform::NintendoSwitch]);
    DB::table('playthroughs')->where('id', $mobile->id)->update(['platform' => 'mobile']);

    (require database_path('migrations/2026_10_26_182349_move_mobile_playthroughs_to_other_platform.php'))->up();

    expect($mobile->fresh()->platform)->toBe(Platform::Other)
        ->and($switch->fresh()->platform)->toBe(Platform::NintendoSwitch);
});
