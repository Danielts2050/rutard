<?php

namespace Tests\Feature;

use App\Models\Ruta;
use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RutaTest extends TestCase
{
    use RefreshDatabase;

    private User $driver;
    private User $admin;
    private Vehicle $vehicle;
    private string $driverToken;
    private string $adminToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\UserSeeder::class);

        $this->driver = User::where('email', 'carlos@rutatransporte.com')->first();
        $this->admin = User::where('email', 'admin@rutatransporte.com')->first();

        $driverRole = Role::where('name', 'Chofer')->first();
        $this->vehicle = Vehicle::factory()->create([
            'chofer_id' => $this->driver->id,
            'estado' => 'activo',
        ]);

        $this->driverToken = $this->driver->createToken('test')->plainTextToken;
        $this->adminToken = $this->admin->createToken('test')->plainTextToken;
    }

    public function test_driver_can_start_a_route(): void
    {
        $response = $this->withToken($this->driverToken)
            ->postJson('/api/rutas/iniciar', [
                'vehiculo_id' => $this->vehicle->id,
                'latitud' => 19.432608,
                'longitud' => -99.133209,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Ruta iniciada exitosamente')
            ->assertJsonStructure(['ruta' => ['id', 'chofer_id', 'vehiculo_id', 'hora_inicio', 'latitud_inicio', 'longitud_inicio', 'estado']]);

        $this->assertDatabaseHas('rutas', [
            'chofer_id' => $this->driver->id,
            'estado' => 'activa',
        ]);
    }

    public function test_admin_cannot_start_a_route(): void
    {
        $response = $this->withToken($this->adminToken)
            ->postJson('/api/rutas/iniciar', [
                'vehiculo_id' => $this->vehicle->id,
                'latitud' => 19.432608,
                'longitud' => -99.133209,
            ]);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'Solo los choferes pueden iniciar rutas.');
    }

    public function test_driver_cannot_start_two_active_routes(): void
    {
        Ruta::create([
            'chofer_id' => $this->driver->id,
            'vehiculo_id' => $this->vehicle->id,
            'hora_inicio' => now()->subHour(),
            'latitud_inicio' => 19.432608,
            'longitud_inicio' => -99.133209,
            'estado' => 'activa',
        ]);

        $response = $this->withToken($this->driverToken)
            ->postJson('/api/rutas/iniciar', [
                'vehiculo_id' => $this->vehicle->id,
                'latitud' => 19.451054,
                'longitud' => -99.153969,
            ]);

        $response->assertStatus(409)
            ->assertJsonPath('message', 'Ya tienes una ruta activa. Finalízala antes de iniciar otra.');
    }

    public function test_driver_can_finish_a_route(): void
    {
        $ruta = Ruta::create([
            'chofer_id' => $this->driver->id,
            'vehiculo_id' => $this->vehicle->id,
            'hora_inicio' => now()->subHours(2),
            'latitud_inicio' => 19.432608,
            'longitud_inicio' => -99.133209,
            'estado' => 'activa',
        ]);

        $response = $this->withToken($this->driverToken)
            ->putJson("/api/rutas/{$ruta->id}/finalizar", [
                'latitud' => 19.451054,
                'longitud' => -99.153969,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Ruta finalizada exitosamente');

        $this->assertDatabaseHas('rutas', [
            'id' => $ruta->id,
            'estado' => 'finalizada',
            'latitud_fin' => 19.4510540,
            'longitud_fin' => -99.1539690,
        ]);

        $ruta->refresh();
        $this->assertNotNull($ruta->hora_fin);
        $this->assertNotNull($ruta->duracion_minutos);
        $this->assertGreaterThan(0, $ruta->duracion_minutos);
    }

    public function test_driver_cannot_finish_another_drivers_route(): void
    {
        $otherDriver = User::where('email', 'maria@rutatransporte.com')->first();
        $otherVehicle = Vehicle::factory()->create(['chofer_id' => $otherDriver->id]);

        $ruta = Ruta::create([
            'chofer_id' => $otherDriver->id,
            'vehiculo_id' => $otherVehicle->id,
            'hora_inicio' => now()->subHour(),
            'latitud_inicio' => 19.432608,
            'longitud_inicio' => -99.133209,
            'estado' => 'activa',
        ]);

        $response = $this->withToken($this->driverToken)
            ->putJson("/api/rutas/{$ruta->id}/finalizar", [
                'latitud' => 19.451054,
                'longitud' => -99.153969,
            ]);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'Esta ruta no te pertenece.');

        $this->assertDatabaseHas('rutas', [
            'id' => $ruta->id,
            'estado' => 'activa',
        ]);
    }

    public function test_cannot_finish_already_finished_route(): void
    {
        $ruta = Ruta::create([
            'chofer_id' => $this->driver->id,
            'vehiculo_id' => $this->vehicle->id,
            'hora_inicio' => now()->subHours(3),
            'latitud_inicio' => 19.432608,
            'longitud_inicio' => -99.133209,
            'hora_fin' => now()->subHours(2),
            'latitud_fin' => 19.451054,
            'longitud_fin' => -99.153969,
            'duracion_minutos' => 60,
            'estado' => 'finalizada',
        ]);

        $response = $this->withToken($this->driverToken)
            ->putJson("/api/rutas/{$ruta->id}/finalizar", [
                'latitud' => 19.451054,
                'longitud' => -99.153969,
            ]);

        $response->assertStatus(409)
            ->assertJsonPath('message', 'Esta ruta ya fue finalizada.');
    }

    public function test_unauthenticated_user_cannot_access_rutas(): void
    {
        $this->postJson('/api/rutas/iniciar', [])->assertStatus(401);
        $this->putJson('/api/rutas/1/finalizar', [])->assertStatus(401);
    }
}
