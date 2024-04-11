<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CocheController;

Route::middleware('auth:sanctum')->group(function () {
    Route::controller(CocheController::class)->group(function () {
        Route::get('/coches/{user_id}', 'show')->whereNumber('user_id');
        Route::get('/coches', 'index');
        Route::post('/coche/nuevo', 'store');
        Route::get('/coche/sensors/{coche_id}', 'showSensors')->whereNumber('coche_id');
        // Route::get('/coches/sensor/{user_id}', 'showAll')->whereNumber('user_id');
    });
});
Route::get('/checkCoches', function() { return 'ok'; });