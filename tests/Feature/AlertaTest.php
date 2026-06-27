<?php

namespace Tests\Feature;

use App\Models\AlertConfig;
use App\Models\Alerta;
use App\Models\Role;
use App\Models\Ruta;
use App\Models\Ubicacion;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\AlertaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlertaTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $driver;
    private Vehicle $vehicle;
    private string $adminToken;
    private string $driverToken;

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

        $this->adminToken = $this->admin->createToken('test')->plainTextToken;
        $this->driverToken = $this->driver->createToken('test')->plainTextToken;
    }

    public function test_alert_config_model_can_get_value(): void
    {
        AlertConfig::create([
            'clave' => 'velocidad_maxima',
            'nombre' => 'Velocidad máxima',
            'valor' => '120',
            'tipo' => 'integer',
            'descripcion' => 'Test',
        ]);

        $this->assertEquals(120, AlertConfig::valor('velocidad_maxima'));
        $this->assertEquals(100, AlertConfig::valor('no_existe', 100));
    }

    public function test_api_can_list_alert_configs(): void
    {
        AlertConfig::create([
            'clave' => 'velocidad_maxima',
            'nombre' => 'Velocidad máxima',
            'valor' => '100',
            'tipo' => 'integer',
            'descripcion' => 'Test',
        ]);

        $response = $this->withToken($this->driverToken)
            ->getJson('/api/alertas/config');

        $response->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function test_api_can_register_device_token(): void
    {
        $response = $this->withToken($this->driverToken)
            ->postJson('/api/dispositivo/token', [
                'device_token' => 'fcm-token-123',
                'platform' => 'android',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Token registrado correctamente.');

        $this->assertDatabaseHas('user_device_tokens', [
            'user_id' => $this->driver->id,
            'device_token' => 'fcm-token-123',
        ]);
    }

    public function test_api_can_unregister_device_token(): void
    {
        \App\Models\UserDeviceToken::create([
            'user_id' => $this->driver->id,
            'device_token' => 'fcm-token-123',
        ]);

        $response = $this->withToken($this->driverToken)
            ->deleteJson('/api/dispositivo/token', [
                'device_token' => 'fcm-token-123',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('user_device_tokens', [
            'device_token' => 'fcm-token-123',
        ]);
    }

    public function test_api_can_list_alertas(): void
    {
        Alerta::create([
            'user_id' => $this->admin->id,
            'tipo' => 'exceso_velocidad',
            'titulo' => 'Test alerta',
            'mensaje' => 'Mensaje de prueba',
            'fecha_alerta' => now(),
        ]);

        $response = $this->withToken($this->adminToken)
            ->getJson('/api/alertas');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_api_can_count_unread_alertas(): void
    {
        Alerta::create([
            'user_id' => $this->admin->id,
            'tipo' => 'exceso_velocidad',
            'titulo' => 'No leída',
            'mensaje' => 'Test',
            'fecha_alerta' => now(),
        ]);

        Alerta::create([
            'user_id' => $this->admin->id,
            'tipo' => 'ruta_finalizada',
            'titulo' => 'Leída',
            'mensaje' => 'Test',
            'leida' => true,
            'fecha_alerta' => now(),
        ]);

        $response = $this->withToken($this->adminToken)
            ->getJson('/api/alertas/no-leidas');

        $response->assertStatus(200)
            ->assertJsonPath('no_leidas', 1);
    }

    public function test_api_can_mark_alerta_as_read(): void
    {
        $alerta = Alerta::create([
            'user_id' => $this->admin->id,
            'tipo' => 'exceso_velocidad',
            'titulo' => 'Test',
            'mensaje' => 'Test',
            'fecha_alerta' => now(),
        ]);

        $response = $this->withToken($this->adminToken)
            ->putJson("/api/alertas/{$alerta->id}/marcar-leida");

        $response->assertStatus(200);
        $this->assertDatabaseHas('alertas', ['id' => $alerta->id, 'leida' => true]);
    }

    public function test_api_can_mark_all_alertas_as_read(): void
    {
        Alerta::create([
            'user_id' => $this->admin->id,
            'tipo' => 'exceso_velocidad',
            'titulo' => 'Test 1',
            'mensaje' => 'Test',
            'fecha_alerta' => now(),
        ]);

        Alerta::create([
            'user_id' => $this->admin->id,
            'tipo' => 'ruta_finalizada',
            'titulo' => 'Test 2',
            'mensaje' => 'Test',
            'fecha_alerta' => now(),
        ]);

        $response = $this->withToken($this->adminToken)
            ->putJson('/api/alertas/marcar-todas-leidas');

        $response->assertStatus(200);
        $this->assertEquals(0, Alerta::where('user_id', $this->admin->id)->where('leida', false)->count());
    }

    public function test_api_cannot_mark_other_users_alerta(): void
    {
        $alerta = Alerta::create([
            'user_id' => $this->admin->id,
            'tipo' => 'exceso_velocidad',
            'titulo' => 'Test',
            'mensaje' => 'Test',
            'fecha_alerta' => now(),
        ]);

        $response = $this->withToken($this->driverToken)
            ->putJson("/api/alertas/{$alerta->id}/marcar-leida");

        $response->assertStatus(403);
    }

    public function test_alerta_service_evaluates_ruta_finalizada(): void
    {
        $ruta = Ruta::factory()->create([
            'chofer_id' => $this->driver->id,
            'vehiculo_id' => $this->vehicle->id,
            'estado' => 'finalizada',
            'hora_inicio' => now()->subHours(2),
            'hora_fin' => now(),
            'duracion_minutos' => 120,
        ]);

        $service = $this->app->make(AlertaService::class);
        $service->evaluarRutaFinalizada($ruta);

        $this->assertDatabaseHas('alertas', [
            'ruta_id' => $ruta->id,
            'tipo' => 'ruta_finalizada',
        ]);
    }

    public function test_alerta_service_evaluates_vehiculo_detenido(): void
    {
        AlertConfig::create([
            'clave' => 'minutos_detenido',
            'nombre' => 'Minutos detenido',
            'valor' => '1',
            'tipo' => 'integer',
        ]);

        $ruta = Ruta::factory()->create([
            'chofer_id' => $this->driver->id,
            'vehiculo_id' => $this->vehicle->id,
            'estado' => 'activa',
        ]);

        Ubicacion::create([
            'ruta_id' => $ruta->id,
            'latitud' => 19.4326,
            'longitud' => -99.1332,
            'velocidad' => 0,
            'fecha_hora' => now()->subMinutes(5),
        ]);

        $service = $this->app->make(AlertaService::class);
        $service->evaluarRuta($ruta);

        $this->assertDatabaseHas('alertas', [
            'ruta_id' => $ruta->id,
            'tipo' => 'vehiculo_detenido',
        ]);
    }

    public function test_alerta_service_evaluates_exceso_velocidad(): void
    {
        AlertConfig::create([
            'clave' => 'velocidad_maxima',
            'nombre' => 'Velocidad máxima',
            'valor' => '50',
            'tipo' => 'integer',
        ]);

        $ruta = Ruta::factory()->create([
            'chofer_id' => $this->driver->id,
            'vehiculo_id' => $this->vehicle->id,
            'estado' => 'activa',
        ]);

        Ubicacion::create([
            'ruta_id' => $ruta->id,
            'latitud' => 19.4326,
            'longitud' => -99.1332,
            'velocidad' => 80,
            'fecha_hora' => now(),
        ]);

        $service = $this->app->make(AlertaService::class);
        $service->evaluarRuta($ruta);

        $this->assertDatabaseHas('alertas', [
            'ruta_id' => $ruta->id,
            'tipo' => 'exceso_velocidad',
        ]);
    }

    public function test_alerta_service_evaluates_salida_ruta(): void
    {
        AlertConfig::create([
            'clave' => 'radio_geocerca_km',
            'nombre' => 'Radio geocerca',
            'valor' => '0.1',
            'tipo' => 'float',
        ]);

        $ruta = Ruta::factory()->create([
            'chofer_id' => $this->driver->id,
            'vehiculo_id' => $this->vehicle->id,
            'estado' => 'activa',
            'latitud_inicio' => 19.4326,
            'longitud_inicio' => -99.1332,
        ]);

        Ubicacion::create([
            'ruta_id' => $ruta->id,
            'latitud' => 19.5,
            'longitud' => -99.2,
            'velocidad' => 60,
            'fecha_hora' => now(),
        ]);

        $service = $this->app->make(AlertaService::class);
        $service->evaluarRuta($ruta);

        $this->assertDatabaseHas('alertas', [
            'ruta_id' => $ruta->id,
            'tipo' => 'salida_ruta',
        ]);
    }

    public function test_alerta_evaluar_todas_processes_all_active_routes(): void
    {
        AlertConfig::create([
            'clave' => 'minutos_detenido',
            'nombre' => 'Minutos detenido',
            'valor' => '5',
            'tipo' => 'integer',
        ]);

        $ruta = Ruta::factory()->create([
            'chofer_id' => $this->driver->id,
            'vehiculo_id' => $this->vehicle->id,
            'estado' => 'activa',
        ]);

        Ubicacion::create([
            'ruta_id' => $ruta->id,
            'latitud' => 19.4326,
            'longitud' => -99.1332,
            'velocidad' => 0,
            'fecha_hora' => now()->subMinutes(10),
        ]);

        $service = $this->app->make(AlertaService::class);
        $service->evaluarTodas();

        $this->assertDatabaseHas('alertas', [
            'ruta_id' => $ruta->id,
            'tipo' => 'vehiculo_detenido',
        ]);
    }

    public function test_admin_can_view_alert_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.alertas.index'))
            ->assertStatus(200)
            ->assertSee('Historial de Alertas');
    }

    public function test_admin_can_view_alert_config(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.alertas.config'))
            ->assertStatus(200)
            ->assertSee('Configuración de Alertas');
    }

    public function test_admin_can_seed_alert_configs(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.alertas.config.seed'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('alert_configs', ['clave' => 'minutos_detenido']);
        $this->assertDatabaseHas('alert_configs', ['clave' => 'velocidad_maxima']);
        $this->assertDatabaseHas('alert_configs', ['clave' => 'radio_geocerca_km']);
        $this->assertDatabaseHas('alert_configs', ['clave' => 'dias_mantenimiento']);
    }

    public function test_admin_can_update_alert_configs(): void
    {
        AlertConfig::create([
            'clave' => 'velocidad_maxima',
            'nombre' => 'Velocidad máxima',
            'valor' => '100',
            'tipo' => 'integer',
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.alertas.config.update'), [
                'configs' => [
                    ['clave' => 'velocidad_maxima', 'valor' => '120'],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertEquals(120, (int) AlertConfig::valor('velocidad_maxima'));
    }

    public function test_admin_can_mark_alerta_as_read(): void
    {
        $alerta = Alerta::create([
            'user_id' => $this->admin->id,
            'tipo' => 'exceso_velocidad',
            'titulo' => 'Test',
            'mensaje' => 'Test',
            'fecha_alerta' => now(),
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.alertas.marcar-leida', $alerta))
            ->assertRedirect();

        $this->assertDatabaseHas('alertas', ['id' => $alerta->id, 'leida' => true]);
    }

    public function test_admin_can_delete_alerta(): void
    {
        $alerta = Alerta::create([
            'user_id' => $this->admin->id,
            'tipo' => 'exceso_velocidad',
            'titulo' => 'Test',
            'mensaje' => 'Test',
            'fecha_alerta' => now(),
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.alertas.destroy', $alerta))
            ->assertRedirect();

        $this->assertDatabaseMissing('alertas', ['id' => $alerta->id]);
    }

    public function test_dashboard_shows_recent_alertas(): void
    {
        Alerta::create([
            'user_id' => $this->admin->id,
            'tipo' => 'exceso_velocidad',
            'titulo' => 'Alerta en dashboard',
            'mensaje' => 'Visible',
            'fecha_alerta' => now(),
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertStatus(200)
            ->assertSee('Alerta en dashboard');
    }

    public function test_artisan_command_runs_successfully(): void
    {
        $this->artisan('alertas:evaluar')
            ->assertSuccessful();
    }
}
