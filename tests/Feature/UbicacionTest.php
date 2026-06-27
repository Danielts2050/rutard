<?php

namespace Tests\Feature;

use App\Models\Ruta;
use App\Models\Role;
use App\Models\Ubicacion;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UbicacionTest extends TestCase
{
    use RefreshDatabase;

    private User $driver;
    private User $admin;
    private Vehicle $vehicle;
    private Ruta $activa;
    private Ruta $finalizada;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\UserSeeder::class);

        $this->driver = User::where('email', 'carlos@rutatransporte.com')->first();
        $this->admin = User::where('email', 'admin@rutatransporte.com')->first();

        $this->vehicle = Vehicle::factory()->create([
            'chofer_id' => $this->driver->id,
            'estado' => 'activo',
        ]);

        $this->activa = Ruta::create([
            'chofer_id' => $this->driver->id,
            'vehiculo_id' => $this->vehicle->id,
            'hora_inicio' => Carbon::now()->subHour(),
            'latitud_inicio' => 19.432608,
            'longitud_inicio' => -99.133209,
            'estado' => 'activa',
        ]);

        $this->finalizada = Ruta::create([
            'chofer_id' => $this->driver->id,
            'vehiculo_id' => $this->vehicle->id,
            'hora_inicio' => Carbon::now()->subDays(2),
            'latitud_inicio' => 19.432608,
            'longitud_inicio' => -99.133209,
            'hora_fin' => Carbon::now()->subDays(2)->addHours(2),
            'latitud_fin' => 19.451054,
            'longitud_fin' => -99.153969,
            'duracion_minutos' => 120,
            'estado' => 'finalizada',
        ]);

        $this->token = $this->driver->createToken('test')->plainTextToken;
    }

    public function test_driver_can_send_location_for_active_route(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/ubicaciones', [
                'ruta_id' => $this->activa->id,
                'latitud' => 19.432608,
                'longitud' => -99.133209,
                'velocidad' => 45.5,
                'fecha_hora' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Ubicación registrada')
            ->assertJsonStructure(['ubicacion' => ['id', 'ruta_id', 'latitud', 'longitud', 'velocidad', 'fecha_hora']]);
    }

    public function test_cannot_send_location_for_finished_route(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/ubicaciones', [
                'ruta_id' => $this->finalizada->id,
                'latitud' => 19.432608,
                'longitud' => -99.133209,
                'velocidad' => 0,
            ]);

        $response->assertStatus(409)
            ->assertJsonPath('message', 'No se puede registrar ubicación: la ruta no está activa.');
    }

    public function test_cannot_send_location_for_another_drivers_route(): void
    {
        $otherDriver = User::where('email', 'maria@rutatransporte.com')->first();
        $otherVehicle = Vehicle::factory()->create(['chofer_id' => $otherDriver->id]);
        $otherRoute = Ruta::create([
            'chofer_id' => $otherDriver->id,
            'vehiculo_id' => $otherVehicle->id,
            'hora_inicio' => Carbon::now()->subHour(),
            'latitud_inicio' => 19.432608,
            'longitud_inicio' => -99.133209,
            'estado' => 'activa',
        ]);

        $response = $this->withToken($this->token)
            ->postJson('/api/ubicaciones', [
                'ruta_id' => $otherRoute->id,
                'latitud' => 19.432608,
                'longitud' => -99.133209,
            ]);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'Esta ruta no te pertenece.');
    }

    public function test_location_requires_valid_coordinates(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/ubicaciones', [
                'ruta_id' => $this->activa->id,
                'latitud' => 200,
                'longitud' => -99.133209,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['latitud']);
    }

    public function test_driver_can_send_bulk_locations(): void
    {
        $puntos = [];
        for ($i = 0; $i < 5; $i++) {
            $puntos[] = [
                'latitud' => 19.432608 + ($i * 0.001),
                'longitud' => -99.133209 + ($i * 0.001),
                'velocidad' => 40 + $i,
                'fecha_hora' => Carbon::now()->addSeconds($i * 30)->format('Y-m-d H:i:s'),
            ];
        }

        $response = $this->withToken($this->token)
            ->postJson('/api/ubicaciones/bulk', [
                'ruta_id' => $this->activa->id,
                'puntos' => $puntos,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', '5 ubicaciones registradas');

        $this->assertEquals(5, Ubicacion::where('ruta_id', $this->activa->id)->count());
    }

    public function test_bulk_rejects_more_than_500_points(): void
    {
        $puntos = array_fill(0, 501, [
            'latitud' => 19.432608,
            'longitud' => -99.133209,
        ]);

        $response = $this->withToken($this->token)
            ->postJson('/api/ubicaciones/bulk', [
                'ruta_id' => $this->activa->id,
                'puntos' => $puntos,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['puntos']);
    }

    public function test_bulk_rejects_for_finished_route(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/ubicaciones/bulk', [
                'ruta_id' => $this->finalizada->id,
                'puntos' => [['latitud' => 19.43, 'longitud' => -99.13]],
            ]);

        $response->assertStatus(409);
    }

    public function test_unauthenticated_user_cannot_send_locations(): void
    {
        $this->postJson('/api/ubicaciones', [])->assertStatus(401);
        $this->postJson('/api/ubicaciones/bulk', [])->assertStatus(401);
    }

    public function test_cleanup_command_removes_old_records(): void
    {
        Ubicacion::create([
            'ruta_id' => $this->finalizada->id,
            'latitud' => 19.432608,
            'longitud' => -99.133209,
            'velocidad' => 0,
            'fecha_hora' => Carbon::now()->subDays(60),
        ]);

        Ubicacion::create([
            'ruta_id' => $this->activa->id,
            'latitud' => 19.432608,
            'longitud' => -99.133209,
            'velocidad' => 0,
            'fecha_hora' => Carbon::now(),
        ]);

        $this->artisan('rastreo:limpiar', ['--dias' => 30])
            ->expectsOutputToContain('Ubicaciones eliminadas: 1')
            ->assertSuccessful();

        $this->assertEquals(1, Ubicacion::count());
    }
}
