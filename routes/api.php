<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MunicipalityController;

Route::get('/health', fn() => response()->json(['ok' => true]));

// públicas
Route::post('/login', [AuthController::class, 'login']);
Route::post('/auth/refresh', [AuthController::class, 'refresh']);

// protegidas
Route::middleware(['auth:api'])->group(function () {

    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // municipalidades
    Route::get('/municipalities', [MunicipalityController::class, 'index'])
        ->middleware('permission:municipality.manage,api');

    Route::get('/municipalities/{municipality}', [MunicipalityController::class, 'show'])
        ->middleware('permission:municipality.manage,api');

    // crear: SOLO admin
    Route::post('/municipalities', [MunicipalityController::class, 'store'])
        ->middleware('role:admin,api');

    Route::put('/municipalities/{municipality}', [MunicipalityController::class, 'update'])
        ->middleware('role:admin,api');
});
