<?php

use Illuminate\Support\Facades\Route;

use Rk\RoutingKit\Entities\RkRoute;

RkRoute::registerRoutes();

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

require __DIR__.'/settings.php';
