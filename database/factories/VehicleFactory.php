<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        $marcas = ['Toyota', 'Nissan', 'Volkswagen', 'Ford', 'Chevrolet', 'Hyundai', 'Kia', 'Mercedes-Benz'];
        $modelos = ['Hilux', 'NP300', 'Amarok', 'Ranger', 'S10', 'Tucson', 'Sportage', 'Sprinter'];

        return [
            'placa' => strtoupper(fake()->bothify('???-###')),
            'marca' => fake()->randomElement($marcas),
            'modelo' => fake()->randomElement($modelos),
            'anio' => fake()->numberBetween(2015, 2025),
            'capacidad' => fake()->randomElement([3, 5, 8, 10, 15]),
            'estado' => fake()->randomElement(['activo', 'inactivo', 'mantenimiento']),
            'chofer_id' => null,
        ];
    }
}
