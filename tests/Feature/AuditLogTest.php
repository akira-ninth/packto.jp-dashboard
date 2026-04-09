<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Phase 13l: 監査ログが各操作で記録されることを確認。
 */
class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    private function makeMaster(): User
    {
        return User::create([
            'name' => 'Master',
            'email' => 'master@packto.jp',
            'password' => Hash::make('correct-pass'),
            'role' => User::ROLE_MASTER,
            'customer_id' => null,
        ]);
    }

    private function makeProPlan(): Plan
    {
        return Plan::create([
            'slug' => 'pro',
            'name' => 'Pro',
            'features' => ['image' => true, 'text' => true],
        ]);
    }

    public function test_login_success_records_audit_log(): void
    {
        $this->makeMaster();

        $this->post('/login', [
            'email' => 'master@packto.jp',
            'password' => 'correct-pass',
        ]);

        $log = AuditLog::where('action', 'auth.login')->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertEquals('master@packto.jp', $log->actor_email);
        $this->assertEquals('master', $log->actor_role);
    }

    public function test_login_failure_records_audit_log(): void
    {
        $this->makeMaster();

        $this->from('/login')->post('/login', [
            'email' => 'master@packto.jp',
            'password' => 'wrong',
        ]);

        $log = AuditLog::where('action', 'auth.login_failed')->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertEquals('master@packto.jp', $log->metadata['email']);
    }

    public function test_customer_create_records_audit_log(): void
    {
        Http::fake(); // KV API mock
        $pro = $this->makeProPlan();
        $master = $this->makeMaster();

        $this->actingAs($master)
            ->post('http://'.config('app.admin_domain').'/customers', [
                'subdomain' => 'newco',
                'display_name' => 'New Co',
                'origin_url' => 'https://newco.example.com',
                'plan_id' => $pro->id,
            ]);

        $log = AuditLog::where('action', 'customer.create')->first();
        $this->assertNotNull($log);
        $this->assertEquals('newco', $log->target_label);
        $this->assertEquals('customer', $log->target_type);
        $this->assertEquals('master@packto.jp', $log->actor_email);
        $this->assertEquals('pro', $log->metadata['plan']);
    }

    public function test_customer_delete_records_audit_log(): void
    {
        Http::fake();
        $pro = $this->makeProPlan();
        $customer = Customer::create([
            'subdomain' => 'rays-hd',
            'display_name' => 'rays-hd',
            'origin_url' => 'https://rays-hd.com',
            'plan_id' => $pro->id,
            'active' => true,
        ]);
        $master = $this->makeMaster();

        $this->actingAs($master)
            ->delete('http://'.config('app.admin_domain').'/customers/'.$customer->id);

        $log = AuditLog::where('action', 'customer.delete')->first();
        $this->assertNotNull($log);
        $this->assertEquals('rays-hd', $log->target_label);
    }

    public function test_master_create_records_audit_log(): void
    {
        $master = $this->makeMaster();

        $this->actingAs($master)
            ->post('http://'.config('app.admin_domain').'/masters', [
                'name' => 'New Master',
                'email' => 'newmaster@packto.jp',
            ]);

        $log = AuditLog::where('action', 'master.create')->first();
        $this->assertNotNull($log);
        $this->assertEquals('newmaster@packto.jp', $log->target_label);
    }

    public function test_password_change_records_audit_log(): void
    {
        $master = $this->makeMaster();

        $this->actingAs($master)->patch('/account/password', [
            'current_password' => 'correct-pass',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ]);

        $log = AuditLog::where('action', 'auth.password_change')->first();
        $this->assertNotNull($log);
        $this->assertEquals('master@packto.jp', $log->target_label);
    }

    public function test_audit_log_index_renders_for_master(): void
    {
        $master = $this->makeMaster();

        // 何かログを作る (login を経由)
        $this->post('/login', [
            'email' => 'master@packto.jp',
            'password' => 'correct-pass',
        ]);

        $response = $this->actingAs($master)
            ->get('http://'.config('app.admin_domain').'/audit-logs');

        $response->assertOk();
        $response->assertSee('監査ログ');
        $response->assertSee('auth.login');
    }

    public function test_audit_log_index_blocked_for_customer(): void
    {
        $pro = $this->makeProPlan();
        $customer = Customer::create([
            'subdomain' => 'rays-hd',
            'display_name' => 'rays-hd',
            'origin_url' => 'https://rays-hd.com',
            'plan_id' => $pro->id,
            'active' => true,
        ]);
        $tenant = User::create([
            'name' => 'tenant',
            'email' => 'rays-hd@packto.jp',
            'password' => Hash::make('test-pass'),
            'role' => User::ROLE_CUSTOMER,
            'customer_id' => $customer->id,
        ]);

        $response = $this->actingAs($tenant)
            ->get('http://'.config('app.admin_domain').'/audit-logs');

        $response->assertForbidden();
    }
}
