<?php

use App\Http\Controllers\Admin\AlertaAdminController;
use App\Http\Controllers\Admin\AlertConfigController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\RutaController as AdminRutaController;
use App\Http\Controllers\Admin\RutaDetalleController;
use App\Http\Controllers\Admin\VehicleAdminController;
use App\Http\Controllers\Admin\WebAuthController as AdminWebAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Chofer\DashboardController as ChoferDashboardController;
use App\Http\Controllers\Chofer\RutaController as ChoferRutaController;
use App\Http\Controllers\PushController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        if (auth()->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        if (auth()->user()->isDriver()) {
            return redirect()->route('chofer.dashboard');
        }
    }
    return redirect()->route('login');
});

// Login
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');

// Chofer routes (authenticated + only drivers)
Route::middleware(['auth', 'driver'])->prefix('chofer')->name('chofer.')->group(function () {
    Route::get('/dashboard', [ChoferDashboardController::class, 'index'])->name('dashboard');
    Route::get('/rutas/iniciar', [ChoferRutaController::class, 'create'])->name('rutas.iniciar');
    Route::post('/rutas', [ChoferRutaController::class, 'store'])->name('rutas.store');
    Route::get('/rutas/activa/{ruta}', [ChoferRutaController::class, 'activa'])->name('rutas.activa');
    Route::post('/rutas/{ruta}/finalizar', [ChoferRutaController::class, 'finalizar'])->name('rutas.finalizar');
    Route::get('/rutas/historial', [ChoferRutaController::class, 'historial'])->name('rutas.historial');
    Route::get('/rutas/{ruta}', [ChoferRutaController::class, 'detalle'])->name('rutas.detalle');
    Route::post('/rutas/{ruta}/ubicaciones', [ChoferRutaController::class, 'ubicaciones'])->name('rutas.ubicaciones');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

// Push subscriptions
Route::middleware('auth')->post('/push/subscribe', [PushController::class, 'subscribe']);
Route::middleware('auth')->post('/push/unsubscribe', [PushController::class, 'unsubscribe']);

// Admin routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminWebAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminWebAuthController::class, 'login'])->name('login.post');

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('/logout', [AdminWebAuthController::class, 'logout'])->name('logout');

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/vehicles', [VehicleAdminController::class, 'index'])->name('vehicles');
        Route::post('/vehicles/{vehicle}/assign', [VehicleAdminController::class, 'assign'])->name('vehicles.assign');
        Route::get('/rutas/mapa-activas', [DashboardController::class, 'mapaActivas'])->name('rutas.mapa-activas');

        Route::get('/rutas/activas', [AdminRutaController::class, 'activas'])->name('rutas.activas');
        Route::get('/rutas/historial', [AdminRutaController::class, 'historial'])->name('rutas.historial');
        Route::get('/rutas/{ruta}', [RutaDetalleController::class, 'show'])->name('rutas.detalle');

        Route::get('/exports', [ExportController::class, 'index'])->name('exports');
        Route::post('/exports/pdf', [ExportController::class, 'pdf'])->name('exports.pdf.post');
        Route::get('/rutas/{ruta}/pdf', [ExportController::class, 'pdf'])->name('exports.pdf');
        Route::get('/exports/excel', [ExportController::class, 'excel'])->name('exports.excel');

        Route::get('/alertas', [AlertaAdminController::class, 'index'])->name('alertas.index');
        Route::post('/alertas/{alerta}/leida', [AlertaAdminController::class, 'marcarLeida'])->name('alertas.marcar-leida');
        Route::post('/alertas/leidas/todas', [AlertaAdminController::class, 'marcarTodasLeidas'])->name('alertas.marcar-todas-leidas');
        Route::delete('/alertas/{alerta}', [AlertaAdminController::class, 'destroy'])->name('alertas.destroy');

        Route::get('/alertas/config', [AlertConfigController::class, 'index'])->name('alertas.config');
        Route::post('/alertas/config', [AlertConfigController::class, 'update'])->name('alertas.config.update');
        Route::post('/alertas/config/seed', [AlertConfigController::class, 'seed'])->name('alertas.config.seed');
    });
});
