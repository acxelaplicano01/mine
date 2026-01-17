<?php

use Illuminate\Support\Facades\Route;

use Rk\RoutingKit\Entities\RkRoute;

RkRoute::registerRoutes();

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('usercontroller_D0Q');

Route::get('/orders/print', function () {
    $ids = explode(',', request('ids'));
    $orders = \App\Models\Order\Orders::with(['customer', 'items.product', 'items.variant', 'envio', 'statusOrder', 'statusPreparedOrder'])
        ->whereIn('id', $ids)
        ->get();
    return view('orders.print', compact('orders'));
})->middleware(['auth'])->name('orders.print');

require __DIR__.'/settings.php';
