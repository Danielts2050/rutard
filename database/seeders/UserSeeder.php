<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'Administrador')->first();
        $driverRole = Role::where('name', 'Chofer')->first();

        User::create([
            'name' => 'Admin Principal',
            'email' => 'admin@rutatransporte.com',
            'password' => 'password',
            'role_id' => $adminRole->id,
        ]);

        User::create([
            'name' => 'Carlos López',
            'email' => 'carlos@rutatransporte.com',
            'password' => 'password',
            'role_id' => $driverRole->id,
        ]);

        User::create([
            'name' => 'María García',
            'email' => 'maria@rutatransporte.com',
            'password' => 'password',
            'role_id' => $driverRole->id,
        ]);
    }
}
