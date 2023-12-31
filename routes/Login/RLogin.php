<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Login\LoginController;

Route::get('/check', function() { return 'ok'; });
Route::post('/login', [LoginController::class, 'login']);
Route::post('/register', [LoginController::class, 'register']);
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/validar/{user_id}', [LoginController::class, 'validar'])->whereNumber('user_id');