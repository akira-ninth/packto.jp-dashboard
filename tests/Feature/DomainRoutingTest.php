<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DomainRoutingTest extends TestCase
{
    use RefreshDatabase;

    private function seedPlansAndUsers(): array
    {
        $pro = Plan::create([
            'slug' => 'pro',
            'name' => 'Pro',
            'features' => ['image' => true, 'text' => true],
        ]);

        $customer = Customer::create([
            'subdomain' => 'rays-hd',
            'display_name' => 'rays-hd',
            'origin_url' => 'https://rays-hd.com',
            'plan_id' => $pro->id,
            'active' => true,
        ]);

        $master = User::create([
            'name' => 'Packto Master',
            'email' => 'master@packto.jp',
            'password' => Hash::make('test-pass'),
            'role' => User::ROLE_MASTER,
            'customer_id' => null,
        ]);

        $tenant = User::create([
            'name' => 'rays-hd admin',
            'email' => 'rays-hd@packto.jp',
            'password' => Hash::make('test-pass'),
            'role' => User::ROLE_CUSTOMER,
            'customer_id' => $customer->id,
        ]);

        return [$master, $tenant, $customer];
    }

    public function test_admin_dashboard_loads_for_master_user(): void
    {
        [$master] = $this->seedPlansAndUsers();

        $response = $this->actingAs($master)
            ->get('http://'.config('app.admin_domain').'/');

        $response->assertOk();
        $response->assertSee('管理ダッシュボード');
    }

    public function test_admin_dashboard_blocked_for_customer_user(): void
    {
        [, $tenant] = $this->seedPlansAndUsers();

        $response = $this->actingAs($tenant)
            ->get('http://'.config('app.admin_domain').'/');

        $response->assertForbidden();
    }

    public function test_tenant_dashboard_loads_for_customer_user(): void
    {
        [, $tenant, $customer] = $this->seedPlansAndUsers();

        $response = $this->actingAs($tenant)
            ->get('http://'.config('app.app_domain').'/');

        $response->assertOk();
        $response->assertSee($customer->display_name);
    }

    public function test_tenant_dashboard_blocked_for_master_user(): void
    {
        [$master] = $this->seedPlansAndUsers();

        $response = $this->actingAs($master)
            ->get('http://'.config('app.app_domain').'/');

        $response->assertForbidden();
    }

    public function test_admin_routes_redirect_unauthenticated_to_login(): void
    {
        $response = $this->get('http://'.config('app.admin_domain').'/');

        $response->assertRedirect(route('login'));
    }

    public function test_customers_index_lists_seeded_customer(): void
    {
        [$master, , $customer] = $this->seedPlansAndUsers();

        $response = $this->actingAs($master)
            ->get('http://'.config('app.admin_domain').'/customers');

        $response->assertOk();
        $response->assertSee($customer->subdomain);
        $response->assertSee($customer->origin_url);
    }
}
