<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Plan;
use App\Models\User;
use App\Services\CloudflareAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Phase 12b: AE SQL クエリの HTTP fake テストと dashboard 連携。
 */
class CloudflareAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private const ACCOUNT_ID = 'test-account';

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.cloudflare.api_token', 'test-token');
        Config::set('services.cloudflare.account_id', self::ACCOUNT_ID);
    }

    private function aeUrl(): string
    {
        return 'https://api.cloudflare.com/client/v4/accounts/'.self::ACCOUNT_ID.'/analytics_engine/sql';
    }

    public function test_get_customer_summary_returns_first_row(): void
    {
        Http::fake([
            $this->aeUrl() => Http::response([
                'data' => [
                    ['reqs' => '42', 'total_bytes' => 1234567],
                ],
            ], 200),
        ]);

        $service = $this->app->make(CloudflareAnalyticsService::class);
        $result = $service->getCustomerSummary('rays-hd', 7);

        $this->assertEquals('42', $result['reqs']);
        $this->assertEquals(1234567, $result['total_bytes']);

        Http::assertSent(function ($request) {
            return $request->method() === 'POST'
                && str_contains($request->body(), "index1 = 'rays-hd'")
                && str_contains($request->body(), "INTERVAL '7' DAY");
        });
    }

    public function test_get_customer_summary_returns_zeros_when_empty(): void
    {
        Http::fake([
            $this->aeUrl() => Http::response(['data' => []], 200),
        ]);

        $service = $this->app->make(CloudflareAnalyticsService::class);
        $result = $service->getCustomerSummary('rays-hd', 7);

        $this->assertEquals('0', $result['reqs']);
        $this->assertEquals(0, $result['total_bytes']);
    }

    public function test_query_failure_returns_empty_summary(): void
    {
        Http::fake([
            $this->aeUrl() => Http::response(['errors' => [['message' => 'kaboom']]], 500),
        ]);

        $service = $this->app->make(CloudflareAnalyticsService::class);
        $result = $service->getCustomerSummary('rays-hd', 7);

        $this->assertEquals('0', $result['reqs']);
        $this->assertEquals(0, $result['total_bytes']);
    }

    public function test_no_token_returns_empty(): void
    {
        Config::set('services.cloudflare.api_token', null);
        Http::fake();

        $service = $this->app->make(CloudflareAnalyticsService::class);
        $result = $service->getCustomerSummary('rays-hd', 7);

        $this->assertEquals('0', $result['reqs']);
        Http::assertNothingSent();
    }

    public function test_get_customer_by_format(): void
    {
        Http::fake([
            $this->aeUrl() => Http::response([
                'data' => [
                    ['format' => 'avif', 'reqs' => '20', 'total_bytes' => 1000000],
                    ['format' => 'webp', 'reqs' => '10', 'total_bytes' => 500000],
                ],
            ], 200),
        ]);

        $service = $this->app->make(CloudflareAnalyticsService::class);
        $rows = $service->getCustomerByFormat('rays-hd', 7);

        $this->assertCount(2, $rows);
        $this->assertEquals('avif', $rows[0]['format']);
        $this->assertEquals('20', $rows[0]['reqs']);
    }

    public function test_tenant_dashboard_renders_usage_when_data_exists(): void
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
        $tenant = User::create([
            'name' => 'rays-hd admin',
            'email' => 'rays-hd@packto.jp',
            'password' => Hash::make('test-pass'),
            'role' => User::ROLE_CUSTOMER,
            'customer_id' => $customer->id,
        ]);

        // 4 つの SQL 呼び出しを順序と関係なくシミュレート (sequence でレスポンスを並べる)
        Http::fake([
            $this->aeUrl() => Http::sequence()
                ->push(['data' => [['reqs' => '100', 'total_bytes' => 5_242_880]]], 200) // summary: 5MB
                ->push(['data' => [['day' => '2026-04-08', 'reqs' => '100', 'total_bytes' => 5_242_880]]], 200) // by day
                ->push(['data' => [['format' => 'avif', 'reqs' => '100', 'total_bytes' => 5_242_880]]], 200) // by format
                ->push(['data' => [['cache_status' => 'MISS', 'reqs' => '100']]], 200), // by cache
        ]);

        $response = $this->actingAs($tenant)
            ->get('http://'.config('app.app_domain').'/');

        $response->assertOk();
        $response->assertSee('使用量サマリ');
        $response->assertSee('100'); // reqs
        $response->assertSee('5.00'); // MB
        $response->assertSee('avif');
    }

    public function test_tenant_dashboard_handles_empty_analytics_gracefully(): void
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
        $tenant = User::create([
            'name' => 'rays-hd admin',
            'email' => 'rays-hd@packto.jp',
            'password' => Hash::make('test-pass'),
            'role' => User::ROLE_CUSTOMER,
            'customer_id' => $customer->id,
        ]);

        Http::fake([
            $this->aeUrl() => Http::sequence()
                ->push(['data' => []], 200)
                ->push(['data' => []], 200)
                ->push(['data' => []], 200)
                ->push(['data' => []], 200),
        ]);

        $response = $this->actingAs($tenant)
            ->get('http://'.config('app.app_domain').'/');

        $response->assertOk();
        $response->assertSee('直近 7 日のリクエストがありません');
    }
}
