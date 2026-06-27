<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlertConfig;
use App\Models\Alerta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlertConfigController extends Controller
{
    public function index(): View
    {
        $configs = AlertConfig::orderBy('clave')->get();
        return view('admin.alertas.config', compact('configs'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'configs' => 'required|array',
            'configs.*.clave' => 'required|string|exists:alert_configs,clave',
            'configs.*.valor' => 'required|string',
        ]);

        foreach ($request->configs as $item) {
            AlertConfig::where('clave', $item['clave'])->update(['valor' => $item['valor']]);
        }

        return redirect()->route('admin.alertas.config')
            ->with('success', 'Configuraciones actualizadas correctamente.');
    }

    public function seed(): RedirectResponse
    {
        $defaults = [
            ['clave' => 'minutos_detenido', 'nombre' => 'Minutos para detectar vehículo detenido', 'valor' => '5', 'tipo' => 'integer', 'descripcion' => 'Tiempo en minutos sin movimiento para generar alerta de vehículo detenido.'],
            ['clave' => 'velocidad_maxima', 'nombre' => 'Velocidad máxima permitida (km/h)', 'valor' => '100', 'tipo' => 'integer', 'descripcion' => 'Velocidad máxima antes de generar alerta de exceso de velocidad.'],
            ['clave' => 'radio_geocerca_km', 'nombre' => 'Radio de geocerca (km)', 'valor' => '2', 'tipo' => 'float', 'descripcion' => 'Distancia máxima desde el inicio de ruta antes de alertar salida de ruta.'],
            ['clave' => 'dias_mantenimiento', 'nombre' => 'Días para alerta de mantenimiento', 'valor' => '7', 'tipo' => 'integer', 'descripcion' => 'Días sin actividad en un vehículo antes de generar alerta de próximo mantenimiento.'],
        ];

        foreach ($defaults as $item) {
            AlertConfig::firstOrCreate(['clave' => $item['clave']], $item);
        }

        return redirect()->route('admin.alertas.config')
            ->with('success', 'Configuraciones por defecto creadas.');
    }
}
