<?php

use Illuminate\Support\Facades\Route;

Route::get('/test', fn() => response()->json(['message' => 'API is working']));

require __DIR__ . '/api/auth.php';

Route::middleware('auth:sanctum')->group(function () {
    require __DIR__ . '/api/order.php';
});
