<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_owner_only_dashboard_information_and_menu(): void
    {
        $owner = User::factory()->create([
            'account_type' => User::ACCOUNT_INTERNAL,
            'role' => User::ROLE_OWNER,
            'is_active' => true,
        ]);

        $response = $this->actingAs($owner)->get(route('admin.dashboard'));

        $response
            ->assertOk()
            ->assertSee('Profit bulan ini')
            ->assertSee('Vendor')
            ->assertSee('Pengguna &amp; Role', false)
            ->assertSee('Periksa pengajuan');
    }

    public function test_admin_can_open_dashboard_without_owner_sensitive_information(): void
    {
        $admin = User::factory()->create([
            'account_type' => User::ACCOUNT_INTERNAL,
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response
            ->assertOk()
            ->assertSee('Pesanan diproses')
            ->assertSee('Buat pengajuan')
            ->assertSee('Pembayaran')
            ->assertDontSee('Profit bulan ini')
            ->assertDontSee('Vendor')
            ->assertDontSee('Pengguna &amp; Role', false);
    }

    public function test_account_without_internal_role_cannot_open_admin_dashboard(): void
    {
        $customer = User::factory()->create([
            'account_type' => User::ACCOUNT_CUSTOMER,
            'role' => null,
            'is_active' => true,
        ]);

        $response = $this->actingAs($customer)->get(route('admin.dashboard'));

        $response->assertForbidden();
    }

    public function test_active_admin_can_login_with_email(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@ettra.test',
            'password' => 'Password123!',
            'account_type' => User::ACCOUNT_INTERNAL,
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $response = $this->post(route('admin.login.store'), [
            'login' => $admin->email,
            'password' => 'Password123!',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }
}
