<?php

use App\Enums\CompletionLevel;
use App\Enums\GameStatus;
use App\Enums\Platform;
use App\Models\Game;
use App\Models\Playthrough;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->payload = [
        'title' => 'Hollow Knight',
        'completed_at' => '2026-03-15',
        'platform' => 'switch',
        'rating' => 5,
        'completion_level' => 'full',
    ];
});

test('guests are redirected to the login screen', function (string $method, string $uri) {
    $this->call($method, $uri)->assertRedirect(route('login'));
})->with([
    'index' => ['GET', '/playthroughs'],
    'create' => ['GET', '/playthroughs/create'],
    'store' => ['POST', '/playthroughs'],
    'edit' => ['GET', '/playthroughs/1/edit'],
    'update' => ['PUT', '/playthroughs/1'],
    'destroy' => ['DELETE', '/playthroughs/1'],
]);

describe('index', function () {
    test('shows the platinum badge only on platinum playthroughs', function () {
        Playthrough::factory()->for(Game::factory()->completed()->for($this->user))->create(['completion_level' => 'full']);

        $this->actingAs($this->user)
            ->get(route('playthroughs.index'))
            ->assertOk()
            ->assertDontSee('data-platinum', false);

        Playthrough::factory()->for(Game::factory()->completed()->for($this->user))->create(['completion_level' => 'platinum']);

        $this->actingAs($this->user)
            ->get(route('playthroughs.index'))
            ->assertOk()
            ->assertSee('data-platinum', false);
    });

    test('lists only the playthroughs of the authenticated user, newest first', function () {
        Playthrough::factory()->for(Game::factory()->completed()->for($this->user)->state(['title' => 'Antigo']))->create(['completed_at' => '2024-01-10']);
        Playthrough::factory()->for(Game::factory()->completed()->for($this->user)->state(['title' => 'Recente']))->create(['completed_at' => '2025-06-20']);
        Playthrough::factory()->for(Game::factory()->completed()->state(['title' => 'Alheio']))->create();

        $this->actingAs($this->user)
            ->get(route('playthroughs.index'))
            ->assertOk()
            ->assertSeeInOrder(['Recente', 'Antigo'])
            ->assertDontSee('Alheio');
    });

    test('filters by year including the first and last day', function () {
        $game = Game::factory()->completed()->for($this->user)->create();
        $dates = ['2024-12-31' => 'ontem-ano', '2025-01-01' => 'primeiro', '2025-12-31' => 'ultimo', '2026-01-01' => 'depois'];

        foreach ($dates as $date => $platform) {
            Playthrough::factory()->for($game)->create(['completed_at' => $date]);
        }

        $response = $this->actingAs($this->user)->get(route('playthroughs.index', ['year' => 2025]));

        $response->assertOk();
        expect($response->viewData('playthroughs')->pluck('completed_at')->map->toDateString()->all())
            ->toBe(['2025-12-31', '2025-01-01']);
    });

    test('offers the years that have playthroughs', function () {
        $game = Game::factory()->completed()->for($this->user)->create();
        Playthrough::factory()->for($game)->create(['completed_at' => '2024-05-01']);
        Playthrough::factory()->for($game)->create(['completed_at' => '2024-06-01']);
        Playthrough::factory()->for($game)->create(['completed_at' => '2026-02-01']);

        $this->actingAs($this->user)
            ->get(route('playthroughs.index'))
            ->assertViewHas('years', fn ($years) => $years->all() === [2026, 2024]);
    });
});

describe('create', function () {
    test('prefills the title of one of the user games', function () {
        $game = Game::factory()->for($this->user)->create(['title' => 'Celeste']);

        $this->actingAs($this->user)
            ->get(route('playthroughs.create', ['game' => $game->id]))
            ->assertOk()
            ->assertViewHas('title', 'Celeste');
    });

    test('does not prefill the title of a game of another user', function () {
        $game = Game::factory()->create(['title' => 'Segredo']);

        $this->actingAs($this->user)
            ->get(route('playthroughs.create', ['game' => $game->id]))
            ->assertOk()
            ->assertViewHas('title', null)
            ->assertDontSee('Segredo');
    });
});

describe('store', function () {
    test('saves the chosen cover on the game created by the playthrough', function () {
        $this->actingAs($this->user)
            ->post(route('playthroughs.store'), [...$this->payload, 'rawg_id' => 7, 'cover_url' => 'https://media.rawg.io/hk.jpg'])
            ->assertSessionHasNoErrors();

        expect($this->user->games()->sole())
            ->rawg_id->toBe(7)
            ->cover_url->toBe('https://media.rawg.io/hk.jpg');
    });

    test('rejects a cover link that is not https', function () {
        $this->actingAs($this->user)
            ->post(route('playthroughs.store'), [...$this->payload, 'cover_url' => 'javascript:alert(1)'])
            ->assertSessionHasErrors('cover_url');

        expect(Game::count())->toBe(0);
    });

    test('creates the game and marks it as completed when the title is new', function () {
        $this->actingAs($this->user)
            ->post(route('playthroughs.store'), $this->payload)
            ->assertRedirect(route('playthroughs.index'));

        $game = $this->user->games()->sole();
        $playthrough = $game->playthroughs()->sole();

        expect($game->title)->toBe('Hollow Knight')
            ->and($game->status)->toBe(GameStatus::Completed)
            ->and($playthrough->completed_at->toDateString())->toBe('2026-03-15')
            ->and($playthrough->platform)->toBe(Platform::NintendoSwitch)
            ->and($playthrough->rating)->toBe(5)
            ->and($playthrough->completion_level)->toBe(CompletionLevel::Full);
    });

    test('reuses a backlog game with the same title instead of duplicating it', function () {
        $game = Game::factory()->for($this->user)->create(['title' => 'Hollow Knight']);

        $this->actingAs($this->user)->post(route('playthroughs.store'), $this->payload);

        expect($this->user->games()->count())->toBe(1)
            ->and($game->fresh()->status)->toBe(GameStatus::Completed)
            ->and($game->playthroughs()->count())->toBe(1);
    });

    test('adds another playthrough when the game was already completed', function () {
        $game = Game::factory()->completed()->for($this->user)->has(Playthrough::factory())->create(['title' => 'Hollow Knight']);

        $this->actingAs($this->user)->post(route('playthroughs.store'), $this->payload);

        expect($game->playthroughs()->count())->toBe(2)
            ->and($this->user->games()->count())->toBe(1);
    });

    test('does not attach the playthrough to a game of another user with the same title', function () {
        $other = Game::factory()->create(['title' => 'Hollow Knight']);

        $this->actingAs($this->user)->post(route('playthroughs.store'), $this->payload);

        expect($other->playthroughs()->count())->toBe(0)
            ->and($other->fresh()->status)->toBe(GameStatus::Backlog)
            ->and($this->user->games()->sole()->playthroughs()->count())->toBe(1);
    });

    test('requires every field', function () {
        $this->actingAs($this->user)
            ->post(route('playthroughs.store'), [])
            ->assertSessionHasErrors([
                'title' => 'O campo título é obrigatório.',
                'completed_at' => 'O campo data de conclusão é obrigatório.',
                'platform' => 'O campo plataforma é obrigatório.',
                'rating' => 'O campo nota é obrigatório.',
                'completion_level' => 'O campo nível de conclusão é obrigatório.',
            ]);

        expect(Game::count())->toBe(0);
    });

    test('rejects a completion date in the future', function () {
        $this->actingAs($this->user)
            ->post(route('playthroughs.store'), [...$this->payload, 'completed_at' => now()->addDay()->toDateString()])
            ->assertSessionHasErrors('completed_at');
    });

    test('rejects a rating outside 1 to 5', function (int $rating) {
        $this->actingAs($this->user)
            ->post(route('playthroughs.store'), [...$this->payload, 'rating' => $rating])
            ->assertSessionHasErrors('rating');
    })->with([
        'below the minimum' => 0,
        'above the maximum' => 6,
    ]);

    test('rejects an unknown platform or completion level', function (string $field) {
        $this->actingAs($this->user)
            ->post(route('playthroughs.store'), [...$this->payload, $field => 'inexistente'])
            ->assertSessionHasErrors($field);
    })->with(['platform', 'completion_level']);
});

describe('update', function () {
    test('changes the playthrough details', function () {
        $playthrough = Playthrough::factory()->for(Game::factory()->completed()->for($this->user))->create();

        $this->actingAs($this->user)
            ->put(route('playthroughs.update', $playthrough), [
                'completed_at' => '2025-08-01',
                'platform' => 'pc',
                'rating' => 4,
                'completion_level' => 'platinum',
            ])
            ->assertRedirect(route('playthroughs.index'));

        expect($playthrough->fresh())
            ->completed_at->toDateString()->toBe('2025-08-01')
            ->platform->toBe(Platform::Pc)
            ->rating->toBe(4)
            ->completion_level->toBe(CompletionLevel::Platinum);
    });

    test('rejects invalid details', function () {
        $playthrough = Playthrough::factory()->for(Game::factory()->completed()->for($this->user))->create();

        $this->actingAs($this->user)
            ->put(route('playthroughs.update', $playthrough), [])
            ->assertSessionHasErrors(['completed_at', 'platform', 'rating', 'completion_level']);
    });

    test('returns 404 for a playthrough of another user', function () {
        $playthrough = Playthrough::factory()->create(['rating' => 2]);

        $this->actingAs($this->user)
            ->put(route('playthroughs.update', $playthrough), [
                'completed_at' => '2025-08-01',
                'platform' => 'pc',
                'rating' => 5,
                'completion_level' => 'full',
            ])
            ->assertNotFound();

        expect($playthrough->fresh()->rating)->toBe(2);
    });
});

describe('edit', function () {
    test('returns 404 for a playthrough of another user', function () {
        $this->actingAs($this->user)
            ->get(route('playthroughs.edit', Playthrough::factory()->create()))
            ->assertNotFound();
    });

    test('renders the form for the owner', function () {
        $playthrough = Playthrough::factory()->for(Game::factory()->completed()->for($this->user)->state(['title' => 'Celeste']))->create();

        $this->actingAs($this->user)
            ->get(route('playthroughs.edit', $playthrough))
            ->assertOk()
            ->assertSee('Celeste');
    });
});

describe('destroy', function () {
    test('sends the game back to the backlog when its last playthrough is removed', function () {
        $game = Game::factory()->completed()->for($this->user)->create();
        $playthrough = Playthrough::factory()->for($game)->create();

        $this->actingAs($this->user)
            ->delete(route('playthroughs.destroy', $playthrough))
            ->assertRedirect(route('playthroughs.index'));

        expect(Playthrough::count())->toBe(0)
            ->and($game->fresh()->status)->toBe(GameStatus::Backlog);
    });

    test('keeps the game completed when other playthroughs remain', function () {
        $game = Game::factory()->completed()->for($this->user)->create();
        [$first] = Playthrough::factory()->for($game)->count(2)->create();

        $this->actingAs($this->user)->delete(route('playthroughs.destroy', $first));

        expect($game->playthroughs()->count())->toBe(1)
            ->and($game->fresh()->status)->toBe(GameStatus::Completed);
    });

    test('does not change a game that is being replayed', function () {
        $game = Game::factory()->playing()->for($this->user)->create();
        $playthrough = Playthrough::factory()->for($game)->create();

        $this->actingAs($this->user)->delete(route('playthroughs.destroy', $playthrough));

        expect($game->fresh()->status)->toBe(GameStatus::Playing);
    });

    test('returns 404 for a playthrough of another user', function () {
        $playthrough = Playthrough::factory()->create();

        $this->actingAs($this->user)
            ->delete(route('playthroughs.destroy', $playthrough))
            ->assertNotFound();

        expect(Playthrough::count())->toBe(1);
    });
});
