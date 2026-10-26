<?php

use App\Models\Game;
use App\Models\Playthrough;
use App\Models\User;
use Database\Factories\YearlyGoalFactory;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('guests are redirected to the login screen', function (string $uri) {
    $this->get($uri)->assertRedirect(route('login'));
})->with(['index' => '/years', 'show' => '/years/2026']);

describe('index', function () {
    test('counts the playthroughs of the user per year, newest year first', function () {
        $game = Game::factory()->completed()->for($this->user)->create();
        Playthrough::factory()->for($game)->count(2)->create(['completed_at' => '2024-04-01']);
        Playthrough::factory()->for($game)->create(['completed_at' => '2026-01-01']);
        Playthrough::factory()->create(['completed_at' => '2026-02-01']);

        $this->actingAs($this->user)
            ->get(route('years.index'))
            ->assertOk()
            ->assertViewHas('countsByYear', fn ($counts) => $counts->all() === [2026 => 1, 2024 => 2]);
    });

    test('shows an empty state when there are no playthroughs', function () {
        $this->actingAs($this->user)
            ->get(route('years.index'))
            ->assertOk()
            ->assertSee('Nenhum ano ainda');
    });
});

describe('show', function () {
    test('shows the goal of the year with the progress', function () {
        $game = Game::factory()->completed()->for($this->user)->create();
        Playthrough::factory()->for($game)->count(3)->create(['completed_at' => '2026-02-10']);
        Playthrough::factory()->for($game)->create(['completed_at' => '2025-02-10']);
        $this->user->yearlyGoals()->create(['year' => 2026, 'target' => 12]);
        $this->user->yearlyGoals()->create(['year' => 2025, 'target' => 1]);

        $this->actingAs($this->user)
            ->get(route('years.show', 2026))
            ->assertOk()
            ->assertViewHas('goal', fn ($goal) => $goal['target'] === 12 && $goal['completed'] === 3)
            ->assertSee('Meta de 2026')
            ->assertSee('aria-valuenow="3"', false)
            ->assertSee('Faltam 9')
            ->assertDontSee('Meta de 2025')
            ->assertDontSee('Meta anual não definida.');
    });

    test('shows the goal of a past year as reached when it was', function () {
        $game = Game::factory()->completed()->for($this->user)->create();
        Playthrough::factory()->for($game)->count(2)->create(['completed_at' => '2024-05-10']);
        $this->user->yearlyGoals()->create(['year' => 2024, 'target' => 2]);

        $this->actingAs($this->user)
            ->get(route('years.show', 2024))
            ->assertOk()
            ->assertSee('Meta batida!');
    });

    test('says the goal is not set when the year has none or only another user has one', function () {
        YearlyGoalFactory::new()->create(['year' => 2026]);

        $this->actingAs($this->user)
            ->get(route('years.show', 2026))
            ->assertOk()
            ->assertViewHas('goal', null)
            ->assertSee('Meta de 2026')
            ->assertSee('Meta anual não definida.')
            ->assertDontSee('role="progressbar"', false);
    });

    test('shows the platinum badge only on platinum playthroughs', function () {
        $game = Game::factory()->completed()->for($this->user)->create();
        Playthrough::factory()->for($game)->create(['completed_at' => '2026-02-10', 'completion_level' => 'full']);

        $this->actingAs($this->user)->get(route('years.show', 2026))->assertOk()->assertDontSee('data-platinum', false);

        Playthrough::factory()->for($game)->create(['completed_at' => '2026-03-10', 'completion_level' => 'platinum']);

        $this->actingAs($this->user)->get(route('years.show', 2026))->assertOk()->assertSee('data-platinum', false);
    });

    test('groups the playthroughs of the year by month, with all twelve months', function () {
        $game = Game::factory()->completed()->for($this->user)->create();
        Playthrough::factory()->for($game)->create(['completed_at' => '2026-01-01']);
        Playthrough::factory()->for($game)->count(2)->create(['completed_at' => '2026-03-20']);
        Playthrough::factory()->for($game)->create(['completed_at' => '2026-12-31']);
        Playthrough::factory()->for($game)->create(['completed_at' => '2025-12-31']);
        Playthrough::factory()->create(['completed_at' => '2026-03-01']);

        $response = $this->actingAs($this->user)->get(route('years.show', 2026));

        $response->assertOk()
            ->assertViewHas('total', 4)
            ->assertViewHas('timeline', fn ($timeline) => $timeline->keys()->all() === range(1, 12)
                && $timeline->map->count()->all() === [1 => 1, 2 => 0, 3 => 2, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 1])
            ->assertSee('Março');
    });

    test('shows the timeline of a year without playthroughs', function () {
        $this->actingAs($this->user)
            ->get(route('years.show', 2020))
            ->assertOk()
            ->assertViewHas('total', 0);
    });

    test('returns 404 for a year outside the supported range', function (string $year) {
        $this->actingAs($this->user)
            ->get("/years/{$year}")
            ->assertNotFound();
    })->with([
        'too old' => '1969',
        'too far in the future' => '2101',
        'not a number' => 'abc',
    ]);
});
