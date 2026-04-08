<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Phase 11/13f: AdminCustomerController が CRUD のたびに Cloudflare KV REST API を
 * 叩いていることを HTTP モックで確認する。
 */
class CloudflareKvSyncTest extends TestCase
{
    use RefreshDatabase;

    private const ACCOUNT_ID = 'test-account';
    private const NAMESPACE_ID = 'test-namespace';
    private const API_TOKEN = 'test-token';

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.cloudflare.api_token', self::API_TOKEN);
        Config::set('services.cloudflare.account_id', self::ACCOUNT_ID);
        Config::set('services.cloudflare.kv_customers_namespace_id', self::NAMESPACE_ID);
    }

    private function actingAsMaster(): User
    {
        return User::create([
            'name' => 'Master',
            'email' => 'master@packto.jp',
            'password' => Hash::make('test-pass'),
            'role' => User::ROLE_MASTER,
            'customer_id' => null,
        ]);
    }

    private function makePlan(string $slug, bool $text): Plan
    {
        return Plan::create([
            'slug' => $slug,
            'name' => ucfirst($slug),
            'features' => ['image' => true, 'text' => $text],
        ]);
    }

    private function endpointFor(string $key): string
    {
        return 'https://api.cloudflare.com/client/v4/accounts/'.self::ACCOUNT_ID
            .'/storage/kv/namespaces/'.self::NAMESPACE_ID.'/values/'.$key;
    }

    public function test_store_writes_to_kv(): void
    {
        Http::fake([
            $this->endpointFor('newco') => Http::response(['success' => true], 200),
        ]);

        $pro = $this->makePlan('pro', true);
        $master = $this->actingAsMaster();

        $response = $this->actingAs($master)
            ->post('http://'.config('app.admin_domain').'/customers', [
                'subdomain' => 'newco',
                'display_name' => 'New Co',
                'origin_url' => 'https://newco.example.com',
                'plan_id' => $pro->id,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('customers', ['subdomain' => 'newco']);

        Http::assertSent(function ($request) use ($pro) {
            return $request->method() === 'PUT'
                && $request->url() === $this->endpointFor('newco')
                && $request->hasHeader('Authorization', 'Bearer '.self::API_TOKEN)
                && $request->body() === json_encode([
                    'origin' => 'https://newco.example.com',
                    'plan' => $pro->slug,
                ], JSON_UNESCAPED_SLASHES);
        });
    }

    public function test_update_writes_to_kv_with_new_values(): void
    {
        $basic = $this->makePlan('basic', false);
        $pro = $this->makePlan('pro', true);
        $customer = Customer::create([
            'subdomain' => 'rays-hd',
            'display_name' => 'rays-hd',
            'origin_url' => 'https://rays-hd.com',
            'plan_id' => $pro->id,
            'active' => true,
        ]);

        Http::fake([
            $this->endpointFor('rays-hd') => Http::response(['success' => true], 200),
        ]);

        $master = $this->actingAsMaster();

        $response = $this->actingAs($master)
            ->patch('http://'.config('app.admin_domain').'/customers/'.$customer->id, [
                'display_name' => 'rays-hd renamed',
                'origin_url' => 'https://rays-hd.example.org',
                'plan_id' => $basic->id,
                'active' => '1',
            ]);

        $response->assertRedirect();

        Http::assertSent(function ($request) use ($basic) {
            return $request->method() === 'PUT'
                && $request->url() === $this->endpointFor('rays-hd')
                && $request->body() === json_encode([
                    'origin' => 'https://rays-hd.example.org',
                    'plan' => $basic->slug,
                ], JSON_UNESCAPED_SLASHES);
        });
    }

    public function test_destroy_deletes_from_kv(): void
    {
        $pro = $this->makePlan('pro', true);
        $customer = Customer::create([
            'subdomain' => 'rays-hd',
            'display_name' => 'rays-hd',
            'origin_url' => 'https://rays-hd.com',
            'plan_id' => $pro->id,
            'active' => true,
        ]);

        Http::fake([
            $this->endpointFor('rays-hd') => Http::response(['success' => true], 200),
        ]);

        $master = $this->actingAsMaster();

        $response = $this->actingAs($master)
            ->delete('http://'.config('app.admin_domain').'/customers/'.$customer->id);

        $response->assertRedirect();
        $this->assertDatabaseMissing('customers', ['subdomain' => 'rays-hd']);

        Http::assertSent(function ($request) {
            return $request->method() === 'DELETE'
                && $request->url() === $this->endpointFor('rays-hd');
        });
    }

    public function test_kv_failure_does_not_break_create(): void
    {
        Http::fake([
            $this->endpointFor('newco') => Http::response(['errors' => [['message' => 'kv down']]], 500),
        ]);

        $pro = $this->makePlan('pro', true);
        $master = $this->actingAsMaster();

        $response = $this->actingAs($master)
            ->post('http://'.config('app.admin_domain').'/customers', [
                'subdomain' => 'newco',
                'display_name' => 'New Co',
                'origin_url' => 'https://newco.example.com',
                'plan_id' => $pro->id,
            ]);

        // KV 失敗しても DB 書き込みは成功・リダイレクトする
        $response->assertRedirect();
        $this->assertDatabaseHas('customers', ['subdomain' => 'newco']);
    }

    public function test_kv_skipped_when_not_configured(): void
    {
        Config::set('services.cloudflare.api_token', null);
        Http::fake();

        $pro = $this->makePlan('pro', true);
        $master = $this->actingAsMaster();

        $response = $this->actingAs($master)
            ->post('http://'.config('app.admin_domain').'/customers', [
                'subdomain' => 'newco',
                'display_name' => 'New Co',
                'origin_url' => 'https://newco.example.com',
                'plan_id' => $pro->id,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('customers', ['subdomain' => 'newco']);

        Http::assertNothingSent();
    }

    public function test_store_creates_customer_user_when_create_user_is_set(): void
    {
        Http::fake([
            $this->endpointFor('newco') => Http::response(['success' => true], 200),
        ]);

        $pro = $this->makePlan('pro', true);
        $master = $this->actingAsMaster();

        $response = $this->actingAs($master)
            ->post('http://'.config('app.admin_domain').'/customers', [
                'subdomain' => 'newco',
                'display_name' => 'New Co',
                'origin_url' => 'https://newco.example.com',
                'plan_id' => $pro->id,
                'create_user' => '1',
                'user_name' => 'NewCo Admin',
                'user_email' => 'admin@newco.example.com',
            ]);

        $customer = \App\Models\Customer::where('subdomain', 'newco')->firstOrFail();
        $response->assertRedirect(route('admin.customers.show', $customer));

        // 一回限り表示用に flash session に乗っているか
        $response->assertSessionHas('temp_credentials.email', 'admin@newco.example.com');
        $tempPassword = session('temp_credentials.password');
        $this->assertNotNull($tempPassword);
        $this->assertGreaterThanOrEqual(16, strlen($tempPassword));

        // ユーザが作成され customer に紐付いているか
        $user = \App\Models\User::where('email', 'admin@newco.example.com')->firstOrFail();
        $this->assertEquals(\App\Models\User::ROLE_CUSTOMER, $user->role);
        $this->assertEquals($customer->id, $user->customer_id);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check($tempPassword, $user->password));
    }

    public function test_store_without_create_user_does_not_create_user(): void
    {
        Http::fake([
            $this->endpointFor('newco') => Http::response(['success' => true], 200),
        ]);

        $pro = $this->makePlan('pro', true);
        $master = $this->actingAsMaster();

        $beforeCount = \App\Models\User::count();

        $this->actingAs($master)
            ->post('http://'.config('app.admin_domain').'/customers', [
                'subdomain' => 'newco',
                'display_name' => 'New Co',
                'origin_url' => 'https://newco.example.com',
                'plan_id' => $pro->id,
            ]);

        $this->assertEquals($beforeCount, \App\Models\User::count());
    }

    public function test_store_user_email_required_when_create_user_set(): void
    {
        Http::fake();

        $pro = $this->makePlan('pro', true);
        $master = $this->actingAsMaster();

        $response = $this->actingAs($master)
            ->from('http://'.config('app.admin_domain').'/customers/create')
            ->post('http://'.config('app.admin_domain').'/customers', [
                'subdomain' => 'newco',
                'display_name' => 'New Co',
                'origin_url' => 'https://newco.example.com',
                'plan_id' => $pro->id,
                'create_user' => '1',
                // user_name と user_email を意図的に省略
            ]);

        $response->assertSessionHasErrors(['user_email', 'user_name']);
        $this->assertDatabaseMissing('customers', ['subdomain' => 'newco']);
    }
}
