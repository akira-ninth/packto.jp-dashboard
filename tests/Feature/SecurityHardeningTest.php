<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Phase 13k: セキュリティ強化テスト。
 * - login rate limiting (RateLimiter)
 * - SecurityHeaders middleware
 * - /up の軽量化
 */
class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // 各テストの干渉を避けて RateLimiter をクリア
        RateLimiter::clear('login:master@packto.jp|127.0.0.1');
    }

    private function seedMaster(): User
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

        return User::create([
            'name' => 'Master',
            'email' => 'master@packto.jp',
            'password' => Hash::make('correct-pass'),
            'role' => User::ROLE_MASTER,
            'customer_id' => null,
        ]);
    }

    public function test_login_rate_limiter_blocks_after_5_failures(): void
    {
        $this->seedMaster();

        // 5 回まで失敗できる
        for ($i = 1; $i <= 5; $i++) {
            $response = $this->from('/login')->post('/login', [
                'email' => 'master@packto.jp',
                'password' => 'wrong-pass-'.$i,
            ]);
            $response->assertRedirect('/login');
            $response->assertSessionHasErrors('email');
            $errors = session('errors')->get('email');
            $this->assertStringContainsString('正しくありません', $errors[0]);
        }

        // 6 回目: lockout
        $response = $this->from('/login')->post('/login', [
            'email' => 'master@packto.jp',
            'password' => 'wrong-pass-6',
        ]);
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $errors = session('errors')->get('email');
        $this->assertStringContainsString('多すぎます', $errors[0]);
    }

    public function test_successful_login_clears_rate_limiter(): void
    {
        $this->seedMaster();
        $key = 'login:master@packto.jp|127.0.0.1';

        // 失敗を 4 回
        for ($i = 1; $i <= 4; $i++) {
            $this->from('/login')->post('/login', [
                'email' => 'master@packto.jp',
                'password' => 'wrong',
            ]);
        }
        $this->assertEquals(4, RateLimiter::attempts($key));

        // 正しいパスワードでログイン → 成功 → counter リセット
        $response = $this->post('/login', [
            'email' => 'master@packto.jp',
            'password' => 'correct-pass',
        ]);
        $response->assertRedirect();
        $this->assertAuthenticated();
        $this->assertEquals(0, RateLimiter::attempts($key));
    }

    public function test_security_headers_are_present_on_login_page(): void
    {
        $response = $this->get('/login');

        $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertNotEmpty($response->headers->get('Content-Security-Policy'));
        $this->assertNotEmpty($response->headers->get('Permissions-Policy'));
    }

    public function test_security_headers_present_on_authenticated_pages(): void
    {
        $master = $this->seedMaster();

        $response = $this->actingAs($master)
            ->get('http://'.config('app.admin_domain').'/');

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Content-Security-Policy');
    }

    public function test_csp_allows_jsdelivr_for_chartjs(): void
    {
        $response = $this->get('/login');
        $csp = $response->headers->get('Content-Security-Policy');

        $this->assertStringContainsString('cdn.jsdelivr.net', $csp);
    }

    public function test_up_endpoint_returns_simple_json(): void
    {
        $response = $this->get('/up');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertExactJson(['ok' => true]);

        // 旧 default の HTML が漏洩していないこと
        $body = $response->getContent();
        $this->assertStringNotContainsString('PacktoConsole', $body);
        $this->assertStringNotContainsString('cdn.jsdelivr', $body);
        $this->assertStringNotContainsString('fonts.bunny', $body);
    }
}
