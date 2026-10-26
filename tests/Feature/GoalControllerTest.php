<?php

use App\Models\User;
use App\Models\YearlyGoal;

beforeEach(function () {
    $this->travelTo('2026-06-15');
    $this->user = User::factory()->create();
});

test('guests are redirected to the login screen', function (string $method, string $uri) {
    $this->call($method, $uri)->assertRedirect(route('login'));
})->with([
    'update' => ['PUT', '/goals/2026'],
    'destroy' => ['DELETE', '/goals/2026'],
]);

describe('update', function () {
    test('creates the goal of the year', function () {
        $this->actingAs($this->user)
            ->put(route('goals.update', 2026), ['target' => 24])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('success', 'Meta atualizada.');

        expect($this->user->yearlyGoals()->sole())->year->toBe(2026)->target->toBe(24);
    });

    test('changes an existing goal', function () {
        YearlyGoal::factory()->for($this->user)->create(['year' => 2026, 'target' => 10]);

        $this->actingAs($this->user)->put(route('goals.update', 2026), ['target' => 30]);

        expect($this->user->yearlyGoals()->sole()->target)->toBe(30);
    });

    test('does not touch the goal of another user', function () {
        $other = YearlyGoal::factory()->create(['year' => 2026, 'target' => 10]);

        $this->actingAs($this->user)->put(route('goals.update', 2026), ['target' => 30]);

        expect($other->fresh()->target)->toBe(10)
            ->and($this->user->yearlyGoals()->count())->toBe(1);
    });

    test('rejects an invalid target', function (mixed $target) {
        $this->actingAs($this->user)
            ->put(route('goals.update', 2026), ['target' => $target])
            ->assertSessionHasErrors('target');

        expect(YearlyGoal::count())->toBe(0);
    })->with([
        'empty' => [''],
        'zero' => [0],
        'negative' => [-3],
        'too big' => [1000],
        'text' => ['muitos'],
        'decimal' => [2.5],
    ]);

    test('rejects a year out of range', function (int $year) {
        $this->actingAs($this->user)
            ->put(route('goals.update', $year), ['target' => 10])
            ->assertSessionHasErrors('year');

        expect(YearlyGoal::count())->toBe(0);
    })->with([1999, 2028]);
});

describe('destroy', function () {
    test('removes the goal of the year', function () {
        YearlyGoal::factory()->for($this->user)->create(['year' => 2026]);

        $this->actingAs($this->user)
            ->delete(route('goals.destroy', 2026))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('success', 'Meta removida.');

        expect($this->user->yearlyGoals()->count())->toBe(0);
    });

    test('does not remove the goal of another user', function () {
        $other = YearlyGoal::factory()->create(['year' => 2026]);

        $this->actingAs($this->user)->delete(route('goals.destroy', 2026))->assertRedirect(route('dashboard'));

        expect($other->fresh())->not->toBeNull();
    });
});
