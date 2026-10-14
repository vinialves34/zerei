<?php

use App\Enums\CompletionLevel;
use App\Enums\Platform;
use App\Models\Game;
use App\Models\Playthrough;
use Carbon\CarbonInterface;

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
