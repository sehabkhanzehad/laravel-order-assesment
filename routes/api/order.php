<?php

use App\Http\Controllers\Api\OrderController;
use App\Models\Order;
use Illuminate\Support\Facades\Route;

Route::prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index'])->can('viewAny', Order::class);
    Route::post('/', [OrderController::class, 'store'])->can('create', Order::class);

    Route::get('/{order}', [OrderController::class, 'show'])->can('view', 'order');
    Route::patch('/{order}/status', [OrderController::class, 'updateStatus'])->can('updateStatus', 'order');
    Route::delete('/{order}', [OrderController::class, 'destroy'])->can('delete', 'order');
});
