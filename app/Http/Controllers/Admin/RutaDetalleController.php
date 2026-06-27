<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ruta;
use Illuminate\View\View;

class RutaDetalleController extends Controller
{
    public function show(Ruta $ruta): View
    {
        $ruta->load('chofer', 'vehiculo', 'ubicaciones');

        $puntos = $ruta->ubicaciones()
            ->orderBy('fecha_hora')
            ->get(['latitud', 'longitud', 'fecha_hora', 'velocidad']);

        return view('admin.rutas.detalle', compact('ruta', 'puntos'));
    }
}
