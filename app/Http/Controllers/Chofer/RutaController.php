<?php

namespace App\Http\Controllers\Chofer;

use App\Http\Controllers\Controller;
use App\Models\Ruta;
use App\Models\Ubicacion;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RutaController extends Controller
{
    public function create()
    {
        $vehicles = Vehicle::where('chofer_id', Auth::id())
            ->orWhereNull('chofer_id')
            ->get();

        return view('chofer.rutas.iniciar', compact('vehicles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'latitud_inicio' => 'required|numeric',
            'longitud_inicio' => 'required|numeric',
        ]);

        $activa = Ruta::where('chofer_id', Auth::id())
            ->where('estado', 'activa')
            ->exists();

        if ($activa) {
            return back()->with('error', 'Ya tienes una ruta activa. Finalízala primero.');
        }

        $ruta = Ruta::create([
            'chofer_id' => Auth::id(),
            'vehiculo_id' => $data['vehicle_id'],
            'hora_inicio' => Carbon::now(),
            'latitud_inicio' => $data['latitud_inicio'],
            'longitud_inicio' => $data['longitud_inicio'],
            'estado' => 'activa',
        ]);

        Ubicacion::create([
            'ruta_id' => $ruta->id,
            'latitud' => $data['latitud_inicio'],
            'longitud' => $data['longitud_inicio'],
            'velocidad' => 0,
            'fecha_hora' => Carbon::now(),
        ]);

        return redirect()->route('chofer.rutas.activa', $ruta)
            ->with('success', 'Ruta iniciada correctamente.');
    }

    public function activa(Ruta $ruta)
    {
        if ($ruta->chofer_id !== Auth::id() || $ruta->estado !== 'activa') {
            abort(404);
        }

        $ruta->load('vehiculo', 'ubicaciones');
        return view('chofer.rutas.activa', compact('ruta'));
    }

    public function finalizar(Request $request, Ruta $ruta)
    {
        if ($ruta->chofer_id !== Auth::id() || $ruta->estado !== 'activa') {
            abort(404);
        }

        $data = $request->validate([
            'latitud_fin' => 'required|numeric',
            'longitud_fin' => 'required|numeric',
            'km_recorridos' => 'nullable|numeric|min:0',
        ]);

        $ahora = Carbon::now();
        $inicio = Carbon::parse($ruta->hora_inicio);
        $duracion = (int) $inicio->diffInMinutes($ahora);

        Ubicacion::create([
            'ruta_id' => $ruta->id,
            'latitud' => $data['latitud_fin'],
            'longitud' => $data['longitud_fin'],
            'velocidad' => 0,
            'fecha_hora' => $ahora,
        ]);

        $ruta->update([
            'hora_fin' => $ahora,
            'latitud_fin' => $data['latitud_fin'],
            'longitud_fin' => $data['longitud_fin'],
            'duracion_minutos' => $duracion,
            'km_recorridos' => $data['km_recorridos'] ?? 0,
            'estado' => 'finalizada',
        ]);

        return redirect()->route('chofer.dashboard')
            ->with('success', 'Ruta finalizada correctamente.');
    }

    public function historial()
    {
        $rutas = Ruta::where('chofer_id', Auth::id())
            ->where('estado', 'finalizada')
            ->with('vehiculo')
            ->orderByDesc('hora_inicio')
            ->paginate(20);

        return view('chofer.rutas.historial', compact('rutas'));
    }

    public function detalle(Ruta $ruta)
    {
        if ($ruta->chofer_id !== Auth::id()) {
            abort(404);
        }

        $ruta->load('vehiculo', 'ubicaciones');
        return view('chofer.rutas.detalle', compact('ruta'));
    }

    public function ubicaciones(Request $request, Ruta $ruta)
    {
        if ($ruta->chofer_id !== Auth::id()) {
            abort(404);
        }

        $data = $request->validate([
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'velocidad' => 'nullable|numeric|min:0',
        ]);

        Ubicacion::create([
            'ruta_id' => $ruta->id,
            'latitud' => $data['latitud'],
            'longitud' => $data['longitud'],
            'velocidad' => $data['velocidad'] ?? 0,
            'fecha_hora' => Carbon::now(),
        ]);

        return response()->json(['status' => 'ok']);
    }
}
