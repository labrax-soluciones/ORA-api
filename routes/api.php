<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MunicipalAdminController;
use App\Http\Controllers\MunicipalityController;
use App\Http\Controllers\Parking\ParkingZoneController;
use App\Http\Controllers\Parking\ParkingZoneTypeController;
use App\Http\Controllers\Parking\ParkingZoneTypeScheduleController;
use App\Http\Controllers\PoliceController;
use App\Http\Controllers\TechnicianController;
use App\Http\Controllers\UserController;

Route::get('/health', fn() => response()->json(['ok' => true]));

// públicas
Route::post('/login', [AuthController::class, 'login']);
Route::post('/auth/refresh', [AuthController::class, 'refresh']);

Route::get('/public/municipalities/{slug}', [MunicipalityController::class, 'publicBySlug']);


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

    // PATCH edicion parcial
    Route::patch('/municipalities/{municipality}', [MunicipalityController::class, 'update'])
        ->middleware('role:admin,api');

    // DELETE (SoftDelete)
    Route::delete('/municipalities/{municipality}', [MunicipalityController::class, 'destroy'])
        ->middleware('role:admin,api');

    // RESTORE: /municipalities/{id}/restore
    Route::post('/municipalities/{id}/restore', [MunicipalityController::class, 'restore'])
        ->middleware('role:admin,api');

    // ---- Scoped por municipio (solo valida pertenencia) ----
    Route::prefix('/municipalities/{municipality}')
        ->middleware(['municipality.scope'])
        ->group(function () {

            // 📊 Contadores del dashboard
            Route::get('/stats', [MunicipalityController::class, 'stats']);

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

            // Gestion de zonas y tipos (permiso "zone.manage")
            Route::middleware('permission:zone.manage,api')->group(function () {

                // Tipos de zona de aparcamiento
                Route::get('/parking-zone-types', [ParkingZoneTypeController::class, 'index']);
                Route::post('/parking-zone-types', [ParkingZoneTypeController::class, 'store']);
                Route::get('/parking-zone-types/{type}', [ParkingZoneTypeController::class, 'show']);
                Route::put('/parking-zone-types/{type}', [ParkingZoneTypeController::class, 'update']);
                Route::patch('/parking-zone-types/{type}', [ParkingZoneTypeController::class, 'update']);
                Route::delete('/parking-zone-types/{type}', [ParkingZoneTypeController::class, 'destroy']);

                // Horarios de un tipo
                Route::get('/parking-zone-types/{type}/schedules', [ParkingZoneTypeScheduleController::class, 'index']);
                Route::post('/parking-zone-types/{type}/schedules', [ParkingZoneTypeScheduleController::class, 'store']);
                Route::put('/parking-zone-types/{type}/schedules/{schedule}', [ParkingZoneTypeScheduleController::class, 'update']);
                Route::patch('/parking-zone-types/{type}/schedules/{schedule}', [ParkingZoneTypeScheduleController::class, 'update']);
                Route::delete('/parking-zone-types/{type}/schedules/{schedule}', [ParkingZoneTypeScheduleController::class, 'destroy']);

                // Zonas de aparcamiento
                Route::get('/parking-zones', [ParkingZoneController::class, 'index']);
                Route::post('/parking-zones', [ParkingZoneController::class, 'store']);
                Route::get('/parking-zones/{zone}', [ParkingZoneController::class, 'show']);
                Route::put('/parking-zones/{zone}', [ParkingZoneController::class, 'update']);
                Route::patch('/parking-zones/{zone}', [ParkingZoneController::class, 'update']);
                Route::delete('/parking-zones/{zone}', [ParkingZoneController::class, 'destroy']);
                
                // Zonas - flujo por partes
                Route::post('/parking-zones/basic', [ParkingZoneController::class, 'storeBasic']); // crear básicos sin geometría
                Route::patch('/parking-zones/{zone}/basic', [ParkingZoneController::class, 'updateBasic']); // editar básicos

                // Geometría (solo geometría)
                Route::put('/parking-zones/{zone}/geometry', [ParkingZoneController::class, 'upsertGeometry']); // crear/actualizar geometría
                Route::delete('/parking-zones/{zone}/geometry', [ParkingZoneController::class, 'deleteGeometry']); // eliminar geometría


            });
        });

    // Usuarios superadmin (requiren role admin)
    Route::resource('users', UserController::class)
        ->only(['index', 'show', 'store', 'update', 'destroy'])
        ->middleware('role:admin,api');
});
