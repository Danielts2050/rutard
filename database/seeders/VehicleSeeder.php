<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $choferes = Role::where('name', 'Chofer')->first()?->users;

        if (!$choferes || $choferes->isEmpty()) {
            return;
        }

        $vehiculos = [
            ['placa' => 'ABC-123', 'marca' => 'Toyota',  'modelo' => 'Hilux',    'anio' => 2022, 'capacidad' => 5,  'estado' => 'activo',       'chofer_id' => $choferes[0]->id],
            ['placa' => 'DEF-456', 'marca' => 'Nissan',  'modelo' => 'NP300',    'anio' => 2023, 'capacidad' => 5,  'estado' => 'activo',       'chofer_id' => $choferes[1]->id],
            ['placa' => 'GHI-789', 'marca' => 'Volkswagen', 'modelo' => 'Amarok','anio' => 2021, 'capacidad' => 5,  'estado' => 'mantenimiento', 'chofer_id' => null],
            ['placa' => 'JKL-012', 'marca' => 'Ford',    'modelo' => 'Ranger',   'anio' => 2024, 'capacidad' => 5,  'estado' => 'inactivo',     'chofer_id' => null],
            ['placa' => 'MNO-345', 'marca' => 'Mercedes-Benz', 'modelo' => 'Sprinter', 'anio' => 2023, 'capacidad' => 15, 'estado' => 'activo', 'chofer_id' => null],
        ];

        foreach ($vehiculos as $data) {
            Vehicle::create($data);
        }
    }
}
