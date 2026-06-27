<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ruta;
use App\Models\Ubicacion;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UbicacionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'ruta_id' => 'required|integer|exists:rutas,id',
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
            'velocidad' => 'nullable|numeric|min:0|max:999',
            'fecha_hora' => 'nullable|date_format:Y-m-d H:i:s',
        ]);

        $ruta = Ruta::findOrFail($request->ruta_id);

        if ($ruta->estado !== 'activa') {
            return response()->json([
                'message' => 'No se puede registrar ubicación: la ruta no está activa.',
            ], 409);
        }

        if ($ruta->chofer_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Esta ruta no te pertenece.',
            ], 403);
        }

        $ubicacion = Ubicacion::create([
            'ruta_id' => $request->ruta_id,
            'latitud' => $request->latitud,
            'longitud' => $request->longitud,
            'velocidad' => $request->velocidad ?? 0,
            'fecha_hora' => $request->fecha_hora ?? Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        return response()->json([
            'message' => 'Ubicación registrada',
            'ubicacion' => $ubicacion,
        ], 201);
    }

    public function bulkStore(Request $request): JsonResponse
    {
        $request->validate([
            'ruta_id' => 'required|integer|exists:rutas,id',
            'puntos' => 'required|array|min:1|max:500',
            'puntos.*.latitud' => 'required|numeric|between:-90,90',
            'puntos.*.longitud' => 'required|numeric|between:-180,180',
            'puntos.*.velocidad' => 'nullable|numeric|min:0|max:999',
            'puntos.*.fecha_hora' => 'nullable|date_format:Y-m-d H:i:s',
        ]);

        $ruta = Ruta::findOrFail($request->ruta_id);

        if ($ruta->estado !== 'activa') {
            return response()->json([
                'message' => 'No se puede registrar ubicación: la ruta no está activa.',
            ], 409);
        }

        if ($ruta->chofer_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Esta ruta no te pertenece.',
            ], 403);
        }

        $ahora = Carbon::now()->format('Y-m-d H:i:s');
        $lote = [];

        foreach ($request->puntos as $punto) {
            $lote[] = [
                'ruta_id' => $request->ruta_id,
                'latitud' => $punto['latitud'],
                'longitud' => $punto['longitud'],
                'velocidad' => $punto['velocidad'] ?? 0,
                'fecha_hora' => $punto['fecha_hora'] ?? $ahora,
            ];
        }

        $tamanhoLote = config('rastreo.lote_insercion', 100);

        foreach (array_chunk($lote, $tamanhoLote) as $chunk) {
            Ubicacion::insert($chunk);
        }

        return response()->json([
            'message' => count($lote) . ' ubicaciones registradas',
            'total' => count($lote),
        ], 201);
    }
}
