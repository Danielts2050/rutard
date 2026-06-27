<?php

namespace Database\Seeders;

use App\Models\Ruta;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RutaSeeder extends Seeder
{
    public function run(): void
    {
        $chofer = User::where('email', 'carlos@rutatransporte.com')->first();
        $vehiculo = Vehicle::where('placa', 'ABC-123')->first();

        if (!$chofer || !$vehiculo) {
            return;
        }

        $inicio = Carbon::now()->subHours(3);

        Ruta::create([
            'chofer_id' => $chofer->id,
            'vehiculo_id' => $vehiculo->id,
            'hora_inicio' => $inicio,
            'latitud_inicio' => 19.432608,
            'longitud_inicio' => -99.133209,
            'hora_fin' => $inicio->copy()->addMinutes(45),
            'latitud_fin' => 19.451054,
            'longitud_fin' => -99.153969,
            'duracion_minutos' => 45,
            'estado' => 'finalizada',
        ]);
    }
}
