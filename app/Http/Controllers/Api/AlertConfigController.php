<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AlertConfig;
use Illuminate\Http\JsonResponse;

class AlertConfigController extends Controller
{
    public function index(): JsonResponse
    {
        $configs = AlertConfig::all(['clave', 'nombre', 'valor', 'tipo', 'descripcion']);

        return response()->json($configs);
    }
}
