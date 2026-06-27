<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ruta;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RutaController extends Controller
{
    public function iniciar(Request $request): JsonResponse
    {
        $request->validate([
            'vehiculo_id' => 'required|integer|exists:vehicles,id',
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
        ]);

        $chofer = $request->user();

        if (!$chofer->isDriver()) {
            return response()->json([
                'message' => 'Solo los choferes pueden iniciar rutas.',
            ], 403);
        }

        $activa = Ruta::where('chofer_id', $chofer->id)
            ->where('estado', 'activa')
            ->exists();

        if ($activa) {
            return response()->json([
                'message' => 'Ya tienes una ruta activa. Finalízala antes de iniciar otra.',
            ], 409);
        }

        $ruta = Ruta::create([
            'chofer_id' => $chofer->id,
            'vehiculo_id' => $request->vehiculo_id,
            'hora_inicio' => Carbon::now(),
            'latitud_inicio' => $request->latitud,
            'longitud_inicio' => $request->longitud,
            'estado' => 'activa',
        ]);

        return response()->json([
            'message' => 'Ruta iniciada exitosamente',
            'ruta' => $ruta,
        ], 201);
    }

    public function finalizar(Request $request, Ruta $ruta): JsonResponse
    {
        $chofer = $request->user();

        if (!$chofer->isDriver()) {
            return response()->json([
                'message' => 'Solo los choferes pueden finalizar rutas.',
            ], 403);
        }

        if ($ruta->chofer_id !== $chofer->id) {
            return response()->json([
                'message' => 'Esta ruta no te pertenece.',
            ], 403);
        }

        if ($ruta->estado !== 'activa') {
            return response()->json([
                'message' => 'Esta ruta ya fue finalizada.',
            ], 409);
        }

        $request->validate([
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
        ]);

        $ahora = Carbon::now();
        $inicio = Carbon::parse($ruta->hora_inicio);
        $duracion = (int) $inicio->diffInMinutes($ahora);

        $ruta->update([
            'hora_fin' => $ahora,
            'latitud_fin' => $request->latitud,
            'longitud_fin' => $request->longitud,
            'duracion_minutos' => $duracion,
            'estado' => 'finalizada',
        ]);

        return response()->json([
            'message' => 'Ruta finalizada exitosamente',
            'ruta' => $ruta,
        ]);
    }
}
