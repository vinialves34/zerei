<?php

test('the pages link the favicon and the touch icon', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('favicon.svg', false)
        ->assertSee('favicon.ico', false)
        ->assertSee('apple-touch-icon.png', false);
});

test('the favicon files exist and are not empty', function (string $file) {
    expect(public_path($file))->toBeFile()
        ->and(filesize(public_path($file)))->toBeGreaterThan(0);
})->with(['favicon.svg', 'favicon.ico', 'apple-touch-icon.png']);
