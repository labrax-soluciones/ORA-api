<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MunicipalAdminController;
use App\Http\Controllers\MunicipalityController;
use App\Http\Controllers\PoliceController;
use App\Http\Controllers\TechnicianController;

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

    // ---- Scoped por municipio (solo valida pertenencia) ----
    Route::prefix('/municipalities/{municipality}')
        ->middleware(['municipality.scope'])
        ->group(function () {

            // --- Administradores municipales (requieren municipality.manage) ---
            Route::middleware('permission:municipality.manage,api')->group(function () {
                Route::get('/admins', [MunicipalAdminController::class, 'index']);
                Route::post('/admins', [MunicipalAdminController::class, 'store']);
                Route::put('/admins/{admin}', [MunicipalAdminController::class, 'update']);
                Route::delete('/admins/{admin}', [MunicipalAdminController::class, 'destroy']);
            });


            // Técnicos (requieren municipality.manage)
            Route::middleware('permission:municipality.manage,api')->group(function () {
                Route::get('/technicians', [TechnicianController::class, 'index']);
                Route::post('/technicians', [TechnicianController::class, 'store']);
                Route::put('/technicians/{technician}', [TechnicianController::class, 'update']);
                Route::delete('/technicians/{technician}', [TechnicianController::class, 'destroy']);
            });

            // Policías (requieren police.manage)
            Route::middleware('permission:police.manage,api')->group(function () {
                Route::get('/police', [PoliceController::class, 'index']);
                Route::post('/police', [PoliceController::class, 'store']);
                Route::put('/police/{police}', [PoliceController::class, 'update']);
                Route::delete('/police/{police}', [PoliceController::class, 'destroy']);
            });
        });
});
