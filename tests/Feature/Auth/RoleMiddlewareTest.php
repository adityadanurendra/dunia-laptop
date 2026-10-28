<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/test-role-customer', function () {
            return 'customer-only';
        })->middleware(['auth', 'role:customer']);

        Route::get('/test-role-staff-only', function () {
            return 'staff-only';
        })->middleware(['auth', 'role:staff']);

        Route::get('/test-role-multiple', function () {
            return 'admin-or-superadmin';
        })->middleware(['auth', 'role:admin,super_admin']);
    }

    public function test_guest_is_redirected_to_login_when_accessing_protected_route(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_customer_is_forbidden_from_accessing_admin_dashboard(): void
    {
        $customer = User::factory()->create([
            'role' => UserRole::CUSTOMER,
        ]);

        $response = $this->actingAs($customer)->get('/admin/dashboard');

        $response->assertForbidden();
    }

    public function test_staff_can_access_admin_dashboard(): void
    {
        $staff = User::factory()->staff()->create();

        $response = $this->actingAs($staff)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertSee('Dashboard Operasional');
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertSee('Dashboard Operasional');
    }

    public function test_super_admin_can_access_admin_dashboard(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertSee('Dashboard Operasional');
    }

    public function test_role_middleware_matches_single_role(): void
    {
        $customer = User::factory()->create([
            'role' => UserRole::CUSTOMER,
        ]);

        $this->actingAs($customer)
            ->get('/test-role-customer')
            ->assertOk()
            ->assertSee('customer-only');

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/test-role-customer')
            ->assertForbidden();
    }

    public function test_role_middleware_matches_multiple_roles(): void
    {
        $admin = User::factory()->admin()->create();
        $superAdmin = User::factory()->superAdmin()->create();
        $staff = User::factory()->staff()->create();

        $this->actingAs($admin)
            ->get('/test-role-multiple')
            ->assertOk()
            ->assertSee('admin-or-superadmin');

        $this->actingAs($superAdmin)
            ->get('/test-role-multiple')
            ->assertOk()
            ->assertSee('admin-or-superadmin');

        $this->actingAs($staff)
            ->get('/test-role-multiple')
            ->assertForbidden();
    }
}

