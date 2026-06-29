<?php

namespace App\Http\Controllers\Chofer;

use App\Http\Controllers\Controller;
use App\Models\Ruta;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $rutaActiva = Ruta::where('chofer_id', $user->id)
            ->where('estado', 'activa')
            ->with('vehiculo')
            ->first();

        $stats = [
            'rutas_hoy' => Ruta::where('chofer_id', $user->id)
                ->whereDate('hora_inicio', today())
                ->count(),
            'km_hoy' => Ruta::where('chofer_id', $user->id)
                ->whereDate('hora_inicio', today())
                ->sum('duracion_minutos'),
            'rutas_semana' => Ruta::where('chofer_id', $user->id)
                ->whereBetween('hora_inicio', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
        ];

        return view('chofer.dashboard', compact('user', 'rutaActiva', 'stats'));
    }
}
