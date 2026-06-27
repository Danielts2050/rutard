<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ruta;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalVehiculos = Vehicle::count();
        $totalChoferes = User::whereHas('role', fn($q) => $q->where('name', 'Chofer'))->count();
        $rutasActivas = Ruta::where('estado', 'activa')->count();
        $totalRutas = Ruta::count();

        $rutasPorDia = Ruta::select(
            DB::raw('DATE(hora_inicio) as fecha'),
            DB::raw('COUNT(*) as total')
        )
            ->where('hora_inicio', '>=', now()->subDays(7))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        $vehiculosPorEstado = Vehicle::select('estado', DB::raw('COUNT(*) as total'))
            ->groupBy('estado')
            ->get();

        $ultimasRutas = Ruta::with('chofer', 'vehiculo')
            ->latest('hora_inicio')
            ->take(5)
            ->get();

        return view('admin.dashboard.index', compact(
            'totalVehiculos', 'totalChoferes', 'rutasActivas', 'totalRutas',
            'rutasPorDia', 'vehiculosPorEstado', 'ultimasRutas'
        ));
    }
}
