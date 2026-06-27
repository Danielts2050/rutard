<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VehicleTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $admin;
    private User $driver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\UserSeeder::class);

        $this->admin = User::where('email', 'admin@rutatransporte.com')->first();
        $this->driver = User::where('email', 'carlos@rutatransporte.com')->first();
    }

    // --- WEB (admin panel) ---

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/vehicles')->assertRedirect('/login');
    }

    public function test_can_list_vehicles_on_web(): void
    {
        Vehicle::factory()->count(3)->create();

        $this->actingAs($this->admin)
            ->get('/vehicles')
            ->assertStatus(200)
            ->assertSee('Vehículos');
    }

    public function test_can_see_create_form(): void
    {
        $this->actingAs($this->admin)
            ->get('/vehicles/create')
            ->assertStatus(200)
            ->assertSee('Nuevo vehículo');
    }

    public function test_can_store_vehicle(): void
    {
        $data = [
            'placa' => 'XYZ-999',
            'marca' => 'Toyota',
            'modelo' => 'Corolla',
            'anio' => 2024,
            'capacidad' => 5,
            'estado' => 'activo',
            'chofer_id' => $this->driver->id,
        ];

        $this->actingAs($this->admin)
            ->post('/vehicles', $data)
            ->assertRedirect('/vehicles')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('vehicles', ['placa' => 'XYZ-999']);
    }

    public function test_placa_must_be_unique(): void
    {
        Vehicle::factory()->create(['placa' => 'XYZ-999']);

        $this->actingAs($this->admin)
            ->post('/vehicles', [
                'placa' => 'XYZ-999',
                'marca' => 'Nissan',
                'modelo' => 'Sentra',
                'anio' => 2024,
                'capacidad' => 5,
                'estado' => 'activo',
            ])
            ->assertSessionHasErrors('placa');
    }

    public function test_can_update_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create(['placa' => 'OLD-001']);

        $this->actingAs($this->admin)
            ->put("/vehicles/{$vehicle->id}", [
                'placa' => 'NEW-001',
                'marca' => 'Ford',
                'modelo' => 'Fiesta',
                'anio' => 2023,
                'capacidad' => 5,
                'estado' => 'inactivo',
                'chofer_id' => null,
            ])
            ->assertRedirect('/vehicles')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('vehicles', ['placa' => 'NEW-001']);
        $this->assertDatabaseMissing('vehicles', ['placa' => 'OLD-001']);
    }

    public function test_can_delete_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create();

        $this->actingAs($this->admin)
            ->delete("/vehicles/{$vehicle->id}")
            ->assertRedirect('/vehicles')
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('vehicles', ['id' => $vehicle->id]);
    }

    // --- API ---

    public function test_api_can_list_vehicles(): void
    {
        Vehicle::factory()->count(2)->create();

        $token = $this->admin->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/vehicles')
            ->assertStatus(200)
            ->assertJsonCount(2);
    }

    public function test_api_can_create_vehicle(): void
    {
        $token = $this->admin->createToken('test')->plainTextToken;

        $data = [
            'placa' => 'API-001',
            'marca' => 'Kia',
            'modelo' => 'Rio',
            'anio' => 2024,
            'capacidad' => 5,
            'estado' => 'activo',
            'chofer_id' => $this->driver->id,
        ];

        $this->withToken($token)
            ->postJson('/api/vehicles', $data)
            ->assertStatus(201)
            ->assertJsonPath('vehicle.placa', 'API-001');
    }

    public function test_api_requires_auth(): void
    {
        $this->getJson('/api/vehicles')->assertStatus(401);
        $this->postJson('/api/vehicles', [])->assertStatus(401);
        $this->getJson('/api/vehicles/1')->assertStatus(401);
        $this->putJson('/api/vehicles/1', [])->assertStatus(401);
        $this->deleteJson('/api/vehicles/1')->assertStatus(401);
    }

    public function test_api_can_show_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create();
        $token = $this->admin->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson("/api/vehicles/{$vehicle->id}")
            ->assertStatus(200)
            ->assertJsonPath('placa', $vehicle->placa);
    }

    public function test_api_can_update_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create();
        $token = $this->admin->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->putJson("/api/vehicles/{$vehicle->id}", [
                'placa' => 'UPD-001',
                'marca' => $vehicle->marca,
                'modelo' => $vehicle->modelo,
                'anio' => $vehicle->anio,
                'capacidad' => $vehicle->capacidad,
                'estado' => $vehicle->estado,
                'chofer_id' => $vehicle->chofer_id,
            ])
            ->assertStatus(200)
            ->assertJsonPath('vehicle.placa', 'UPD-001');
    }

    public function test_api_can_delete_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create();
        $token = $this->admin->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->deleteJson("/api/vehicles/{$vehicle->id}")
            ->assertStatus(200);

        $this->assertDatabaseMissing('vehicles', ['id' => $vehicle->id]);
    }
}
