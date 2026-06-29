<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ruta;
use App\Models\Ubicacion;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
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

        $rutasActivasList = Ruta::with('chofer', 'vehiculo')
            ->where('estado', 'activa')
            ->get();

        return view('admin.dashboard.index', compact(
            'totalVehiculos', 'totalChoferes', 'rutasActivas', 'totalRutas',
            'rutasPorDia', 'vehiculosPorEstado', 'ultimasRutas', 'rutasActivasList'
        ));
    }

    public function mapaActivas(): JsonResponse
    {
        $rutas = Ruta::with('chofer', 'vehiculo')
            ->where('estado', 'activa')
            ->get()
            ->map(function ($ruta) {
                $ultima = Ubicacion::where('ruta_id', $ruta->id)
                    ->latest('fecha_hora')
                    ->first();

                return [
                    'id' => $ruta->id,
                    'chofer' => $ruta->chofer?->name ?? 'N/A',
                    'placa' => $ruta->vehiculo?->placa ?? 'N/A',
                    'hora_inicio' => $ruta->hora_inicio?->format('H:i:s'),
                    'lat' => $ultima?->latitud ?? $ruta->latitud_inicio,
                    'lng' => $ultima?->longitud ?? $ruta->longitud_inicio,
                    'ultima_actualizacion' => $ultima?->fecha_hora?->diffForHumans() ?? 'hace un momento',
                ];
            });

        return response()->json($rutas);
    }
}
