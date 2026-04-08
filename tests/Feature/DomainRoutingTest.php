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

    public function test_login_form_renders(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('Packto Console ログイン');
        $response->assertSee('メールアドレス');
    }

    public function test_master_login_redirects_to_admin_dashboard(): void
    {
        $this->seedPlansAndUsers();

        $response = $this->post('/login', [
            'email' => 'master@packto.jp',
            'password' => 'test-pass',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs(User::where('email', 'master@packto.jp')->first());
    }

    public function test_customer_login_redirects_to_tenant_dashboard(): void
    {
        $this->seedPlansAndUsers();

        $response = $this->post('/login', [
            'email' => 'rays-hd@packto.jp',
            'password' => 'test-pass',
        ]);

        $response->assertRedirect(route('tenant.dashboard'));
        $this->assertAuthenticatedAs(User::where('email', 'rays-hd@packto.jp')->first());
    }

    public function test_login_with_wrong_password_fails(): void
    {
        $this->seedPlansAndUsers();

        $response = $this->from('/login')->post('/login', [
            'email' => 'master@packto.jp',
            'password' => 'wrong-pass',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_logout_clears_session(): void
    {
        [$master] = $this->seedPlansAndUsers();

        $response = $this->actingAs($master)->post('/logout');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_account_edit_renders_for_master(): void
    {
        [$master] = $this->seedPlansAndUsers();

        $response = $this->actingAs($master)->get('/account');

        $response->assertOk();
        $response->assertSee('アカウント設定');
        $response->assertSee('master@packto.jp');
    }

    public function test_account_edit_renders_for_customer(): void
    {
        [, $tenant, $customer] = $this->seedPlansAndUsers();

        $response = $this->actingAs($tenant)->get('/account');

        $response->assertOk();
        $response->assertSee($customer->display_name);
    }

    public function test_account_password_can_be_changed(): void
    {
        [$master] = $this->seedPlansAndUsers();

        $response = $this->actingAs($master)->patch('/account/password', [
            'current_password' => 'test-pass',
            'password' => 'new-secure-pass-456',
            'password_confirmation' => 'new-secure-pass-456',
        ]);

        $response->assertRedirect(route('account.edit'));

        $master->refresh();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('new-secure-pass-456', $master->password));
    }

    public function test_account_password_requires_current_password(): void
    {
        [$master] = $this->seedPlansAndUsers();

        $response = $this->actingAs($master)->from('/account')->patch('/account/password', [
            'current_password' => 'wrong-current',
            'password' => 'new-secure-pass-456',
            'password_confirmation' => 'new-secure-pass-456',
        ]);

        $response->assertRedirect('/account');
        $response->assertSessionHasErrors('current_password');
    }

    public function test_account_password_must_be_confirmed(): void
    {
        [$master] = $this->seedPlansAndUsers();

        $response = $this->actingAs($master)->from('/account')->patch('/account/password', [
            'current_password' => 'test-pass',
            'password' => 'new-secure-pass-456',
            'password_confirmation' => 'mismatch',
        ]);

        $response->assertRedirect('/account');
        $response->assertSessionHasErrors('password');
    }
}
