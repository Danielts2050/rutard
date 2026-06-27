<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ruta;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RutaController extends Controller
{
    public function activas(): View
    {
        $rutas = Ruta::with('chofer', 'vehiculo')
            ->where('estado', 'activa')
            ->latest('hora_inicio')
            ->paginate(15);

        return view('admin.rutas.activas', compact('rutas'));
    }

    public function historial(Request $request): View
    {
        $query = Ruta::with('chofer', 'vehiculo')->where('estado', 'finalizada');

        if ($request->filled('chofer')) {
            $query->whereHas('chofer', fn($q) => $q->where('name', 'like', "%{$request->chofer}%"));
        }

        if ($request->filled('vehiculo')) {
            $query->whereHas('vehiculo', fn($q) => $q->where('placa', 'like', "%{$request->vehiculo}%"));
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('hora_inicio', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('hora_inicio', '<=', $request->fecha_hasta);
        }

        $rutas = $query->latest('hora_inicio')->paginate(15)->withQueryString();

        return view('admin.rutas.historial', compact('rutas'));
    }
}
