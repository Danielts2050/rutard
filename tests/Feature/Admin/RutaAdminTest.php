<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\Ruta;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RutaAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $driver;
    private Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);

        $adminRole = Role::where('name', 'Administrador')->first();
        $driverRole = Role::where('name', 'Chofer')->first();

        $this->admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => 'password',
            'role_id' => $adminRole->id,
        ]);

        $this->driver = User::factory()->create([
            'name' => 'Chofer',
            'email' => 'chofer@test.com',
            'password' => 'password',
            'role_id' => $driverRole->id,
        ]);

        $this->vehicle = Vehicle::factory()->create([
            'chofer_id' => $this->driver->id,
            'estado' => 'activo',
        ]);
    }

    public function test_dashboard_shows_stats(): void
    {
        Ruta::factory()->count(3)->create([
            'chofer_id' => $this->driver->id,
            'vehiculo_id' => $this->vehicle->id,
            'estado' => 'finalizada',
        ]);

        Ruta::factory()->create([
            'chofer_id' => $this->driver->id,
            'vehiculo_id' => $this->vehicle->id,
            'estado' => 'activa',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertStatus(200)
            ->assertSee('Dashboard');
    }

    public function test_vehicles_page_lists_vehicles(): void
    {
        Vehicle::factory()->count(3)->create();

        $this->actingAs($this->admin)
            ->get(route('admin.vehicles'))
            ->assertStatus(200)
            ->assertSee('Vehículos');
    }

    public function test_active_routes_page(): void
    {
        Ruta::factory()->create([
            'chofer_id' => $this->driver->id,
            'vehiculo_id' => $this->vehicle->id,
            'estado' => 'activa',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.rutas.activas'))
            ->assertStatus(200)
            ->assertSee('Rutas Activas');
    }

    public function test_route_history_page(): void
    {
        Ruta::factory()->create([
            'chofer_id' => $this->driver->id,
            'vehiculo_id' => $this->vehicle->id,
            'estado' => 'finalizada',
            'hora_inicio' => now(),
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.rutas.historial'))
            ->assertStatus(200)
            ->assertSee('Historial');
    }

    public function test_route_detail_page(): void
    {
        $ruta = Ruta::factory()->create([
            'chofer_id' => $this->driver->id,
            'vehiculo_id' => $this->vehicle->id,
            'estado' => 'finalizada',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.rutas.detalle', $ruta))
            ->assertStatus(200)
            ->assertSee("Ruta #{$ruta->id}");
    }

    public function test_exports_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.exports'))
            ->assertStatus(200)
            ->assertSee('Exportar');
    }

    public function test_route_history_can_filter_by_chofer(): void
    {
        Ruta::factory()->create([
            'chofer_id' => $this->driver->id,
            'vehiculo_id' => $this->vehicle->id,
            'estado' => 'finalizada',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.rutas.historial', ['chofer' => 'Chofer']))
            ->assertStatus(200);
    }

    public function test_route_history_can_filter_by_date(): void
    {
        Ruta::factory()->create([
            'chofer_id' => $this->driver->id,
            'vehiculo_id' => $this->vehicle->id,
            'estado' => 'finalizada',
            'hora_inicio' => now(),
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.rutas.historial', [
                'fecha_desde' => now()->subDay()->format('Y-m-d'),
                'fecha_hasta' => now()->addDay()->format('Y-m-d'),
            ]))
            ->assertStatus(200);
    }
}
