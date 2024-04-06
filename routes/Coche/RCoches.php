<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CocheController;

Route::middleware('auth:sanctum')->group(function () {
    Route::controller(CocheController::class)->group(function () {
        Route::get('/coches/{user_id}', 'show')->whereNumber('user_id');
        Route::get('/coches', 'index');
        Route::get('/cochesSensor/{user_id}', 'showAll')->whereNumber('user_id');
        Route::post('/coches', 'store');
    });
});
Route::get('/checkCoches', function() { return 'ok'; });