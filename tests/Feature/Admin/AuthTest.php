<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $driver;

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
    }

    public function test_show_login_form(): void
    {
        $this->get(route('admin.login'))
            ->assertStatus(200)
            ->assertSee('Inicia sesión');
    }

    public function test_admin_can_login(): void
    {
        $this->post(route('admin.login'), [
            'email' => 'admin@test.com',
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticated();
    }

    public function test_driver_cannot_login_to_admin(): void
    {
        $this->post(route('admin.login'), [
            'email' => 'chofer@test.com',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_invalid_credentials_return_error(): void
    {
        $this->post(route('admin.login'), [
            'email' => 'admin@test.com',
            'password' => 'wrongpassword',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_can_logout(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));

        $this->get(route('admin.vehicles'))
            ->assertRedirect(route('login'));
    }

    public function test_driver_gets_403_on_admin_pages(): void
    {
        $this->actingAs($this->driver)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }
}
