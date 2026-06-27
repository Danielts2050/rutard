<?php

use App\Http\Controllers\Api\AlertaController;
use App\Http\Controllers\Api\AlertConfigController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DeviceTokenController;
use App\Http\Controllers\Api\RutaController;
use App\Http\Controllers\Api\UbicacionController;
use App\Http\Controllers\Api\VehicleController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('api.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/me', [AuthController::class, 'me'])->name('api.me');

    Route::name('api.')->group(function () {
        Route::apiResource('vehicles', VehicleController::class);
    });

    Route::get('/rutas/activa', [RutaController::class, 'activa'])->name('api.rutas.activa');
    Route::post('/rutas/iniciar', [RutaController::class, 'iniciar'])->name('api.rutas.iniciar');
    Route::put('/rutas/{ruta}/finalizar', [RutaController::class, 'finalizar'])->name('api.rutas.finalizar');
    Route::get('/rutas', [RutaController::class, 'historial'])->name('api.rutas.historial');

    Route::post('/ubicaciones', [UbicacionController::class, 'store'])->name('api.ubicaciones.store');
    Route::post('/ubicaciones/bulk', [UbicacionController::class, 'bulkStore'])->name('api.ubicaciones.bulk');

    // Alertas
    Route::get('/alertas', [AlertaController::class, 'index'])->name('api.alertas.index');
    Route::get('/alertas/no-leidas', [AlertaController::class, 'countNoLeidas'])->name('api.alertas.no-leidas');
    Route::put('/alertas/marcar-todas-leidas', [AlertaController::class, 'marcarTodasLeidas'])->name('api.alertas.marcar-todas');
    Route::put('/alertas/{alerta}/marcar-leida', [AlertaController::class, 'marcarLeida'])->name('api.alertas.marcar-leida');

    // Device tokens
    Route::post('/dispositivo/token', [DeviceTokenController::class, 'register'])->name('api.device-token.register');
    Route::delete('/dispositivo/token', [DeviceTokenController::class, 'unregister'])->name('api.device-token.unregister');

    // Alert configs (read-only for drivers)
    Route::get('/alertas/config', [AlertConfigController::class, 'index'])->name('api.alertas.config');
});
