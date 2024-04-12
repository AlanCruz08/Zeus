<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistroController;

Route::middleware('auth:sanctum')->group(function () {
    Route::controller(RegistroController::class)->group(function () {
        Route::get('/ubicacion/{coche_id}', 'ubicacion')->whereNumber('coche_id');
        Route::get('/ledchoque/{coche_id}', 'ledChoque')->whereNumber('coche_id');
        Route::get('/ledcontrol/{coche_id}', 'ledControl')->whereNumber('coche_id');
        Route::post('/ledcontrol/{coche_id}', 'ledControlPost')->whereNumber('coche_id');
        Route::get('/reportedis/{coche_id}', 'reporte')->whereNumber('coche_id');
        Route::post('/control/{coche_id}', 'control')->whereNumber('coche_id');
    });
});
Route::get('/check', function() { return 'ok'; });
