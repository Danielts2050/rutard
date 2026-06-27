<?php

namespace Database\Factories;

use App\Models\Ruta;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class RutaFactory extends Factory
{
    protected $model = Ruta::class;

    public function definition(): array
    {
        return [
            'chofer_id' => User::factory(),
            'vehiculo_id' => Vehicle::factory(),
            'hora_inicio' => now()->subHours(2),
            'latitud_inicio' => $this->faker->latitude,
            'longitud_inicio' => $this->faker->longitude,
            'hora_fin' => now(),
            'latitud_fin' => $this->faker->latitude,
            'longitud_fin' => $this->faker->longitude,
            'duracion_minutos' => 120,
            'estado' => 'finalizada',
        ];
    }
}
