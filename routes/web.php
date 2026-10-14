<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\PlaythroughController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\YearController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::resource('games', GameController::class);
    Route::resource('playthroughs', PlaythroughController::class)->except('show');
    Route::get('/years', [YearController::class, 'index'])->name('years.index');
    Route::get('/years/{year}', [YearController::class, 'show'])->whereNumber('year')->name('years.show');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
