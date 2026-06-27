<?php

use App\Http\Controllers\Admin\AlertaAdminController;
use App\Http\Controllers\Admin\AlertConfigController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\RutaController as AdminRutaController;
use App\Http\Controllers\Admin\RutaDetalleController;
use App\Http\Controllers\Admin\VehicleAdminController;
use App\Http\Controllers\Admin\WebAuthController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('vehicles.index');
});

Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

Route::resource('vehicles', VehicleController::class)->middleware('auth');

// Admin routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [WebAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login'])->name('login.post');

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/vehicles', [VehicleAdminController::class, 'index'])->name('vehicles');

        Route::get('/rutas/activas', [AdminRutaController::class, 'activas'])->name('rutas.activas');
        Route::get('/rutas/historial', [AdminRutaController::class, 'historial'])->name('rutas.historial');
        Route::get('/rutas/{ruta}', [RutaDetalleController::class, 'show'])->name('rutas.detalle');

        Route::get('/exports', [ExportController::class, 'index'])->name('exports');
        Route::post('/exports/pdf', [ExportController::class, 'pdf'])->name('exports.pdf.post');
        Route::get('/rutas/{ruta}/pdf', [ExportController::class, 'pdf'])->name('exports.pdf');
        Route::get('/exports/excel', [ExportController::class, 'excel'])->name('exports.excel');

        // Alertas
        Route::get('/alertas', [AlertaAdminController::class, 'index'])->name('alertas.index');
        Route::post('/alertas/{alerta}/leida', [AlertaAdminController::class, 'marcarLeida'])->name('alertas.marcar-leida');
        Route::post('/alertas/leidas/todas', [AlertaAdminController::class, 'marcarTodasLeidas'])->name('alertas.marcar-todas-leidas');
        Route::delete('/alertas/{alerta}', [AlertaAdminController::class, 'destroy'])->name('alertas.destroy');

        Route::get('/alertas/config', [AlertConfigController::class, 'index'])->name('alertas.config');
        Route::post('/alertas/config', [AlertConfigController::class, 'update'])->name('alertas.config.update');
        Route::post('/alertas/config/seed', [AlertConfigController::class, 'seed'])->name('alertas.config.seed');
    });
});
