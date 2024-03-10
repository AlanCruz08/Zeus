<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CocheController;
use App\Http\Controllers\RegistroController;

Route::middleware('auth:sanctum')->group(function () {
    Route::controller(CocheController::class)->group(function () {
        Route::get('/coches/{user_id}', 'show')->whereNumber('user_id');
        Route::get('/coches', 'index');
        Route::get('/cochesSensor/{user_id}', 'showAll')->whereNumber('user_id');
        Route::post('/coches', 'store');
    });
    Route::controller(RegistroController::class)->group(function () {
        Route::get('/ubicacion/{coche_id}', 'ubicacion')->whereNumber('coche_id');
    });
});
Route::get('/check', function() { return 'ok'; });
