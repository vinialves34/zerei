<?php

arch('services only hold business rules and know nothing about HTTP')
    ->expect('App\Services')
    ->toHaveSuffix('Service')
    ->not->toUse([
        'Illuminate\Http',
        'Illuminate\Routing',
        'Illuminate\View',
        'Illuminate\Support\Facades\Auth',
        'Illuminate\Support\Facades\Gate',
        'Illuminate\Support\Facades\Request',
        'Illuminate\Support\Facades\Redirect',
        'Illuminate\Support\Facades\Session',
        'Illuminate\Support\Facades\View',
    ]);

arch('feature controllers delegate queries and transactions to services')
    ->expect('App\\Http\\Controllers')
    ->not->toUse([
        'Illuminate\\Support\\Facades\\DB',
        'Illuminate\\Database\\Eloquent\\Builder',
    ])
    ->ignoring('App\\Http\\Controllers\\Auth');

arch('services are not called from models')
    ->expect('App\Models')
    ->not->toUse('App\Services');

arch('enums have no dependencies on the application layers')
    ->expect('App\Enums')
    ->not->toUse(['App\Models', 'App\Services', 'App\Http']);
