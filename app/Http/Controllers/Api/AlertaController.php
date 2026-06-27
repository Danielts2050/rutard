<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alerta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlertaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Alerta::with('ruta', 'vehicle')
            ->where('user_id', $request->user()->id)
            ->latest('fecha_alerta');

        if ($request->boolean('no_leidas')) {
            $query->where('leida', false);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        $alertas = $query->paginate(20);

        return response()->json($alertas);
    }

    public function marcarLeida(Alerta $alerta, Request $request): JsonResponse
    {
        if ($alerta->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $alerta->update(['leida' => true]);

        return response()->json(['message' => 'Alerta marcada como leída.']);
    }

    public function marcarTodasLeidas(Request $request): JsonResponse
    {
        Alerta::where('user_id', $request->user()->id)
            ->where('leida', false)
            ->update(['leida' => true]);

        return response()->json(['message' => 'Todas las alertas marcadas como leídas.']);
    }

    public function countNoLeidas(Request $request): JsonResponse
    {
        $count = Alerta::where('user_id', $request->user()->id)
            ->where('leida', false)
            ->count();

        return response()->json(['no_leidas' => $count]);
    }
}
