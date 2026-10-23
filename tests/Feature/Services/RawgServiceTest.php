<?php

use App\Services\RawgService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config(['services.rawg.key' => 'test-key']);
    $this->service = app(RawgService::class);
});

function rawgResponse(): array
{
    return [
        'results' => [
            ['id' => 3498, 'name' => 'Grand Theft Auto V', 'released' => '2013-09-17', 'background_image' => 'https://media.rawg.io/media/games/gta5.jpg'],
            ['id' => 1, 'name' => 'Sem imagem', 'released' => null, 'background_image' => null],
            ['name' => 'Resultado inválido'],
        ],
    ];
}

test('search normalizes the RAWG results and sends the key', function () {
    Http::fake(['api.rawg.io/*' => Http::response(rawgResponse())]);

    $results = $this->service->search('GTA');

    expect($results)->toBe([
        ['id' => 3498, 'name' => 'Grand Theft Auto V', 'released' => '2013-09-17', 'cover_url' => 'https://media.rawg.io/media/games/gta5.jpg'],
        ['id' => 1, 'name' => 'Sem imagem', 'released' => null, 'cover_url' => null],
    ]);

    Http::assertSent(fn ($request) => $request['key'] === 'test-key'
        && $request['search'] === 'gta'
        && $request['page_size'] === 6);
});

test('search caches successful responses', function () {
    Http::fake(['api.rawg.io/*' => Http::response(rawgResponse())]);

    $this->service->search('GTA');
    $this->service->search(' gta ');

    Http::assertSentCount(1);
});

test('search returns an empty list without calling RAWG when the key is missing', function () {
    config(['services.rawg.key' => null]);
    Http::fake();

    expect($this->service->search('GTA'))->toBe([]);

    Http::assertNothingSent();
});

test('search returns an empty list when RAWG fails and does not cache the failure', function () {
    Http::fake(['api.rawg.io/*' => Http::sequence()
        ->push(null, 500)->push(null, 500)->push(null, 500)
        ->push(rawgResponse())]);

    expect($this->service->search('GTA'))->toBe([])
        ->and($this->service->search('GTA'))->toHaveCount(2);
});

test('search returns an empty list when the connection fails', function () {
    Http::fake(['api.rawg.io/*' => fn () => throw new RuntimeException('timeout')]);

    expect($this->service->search('GTA'))->toBe([]);
});
