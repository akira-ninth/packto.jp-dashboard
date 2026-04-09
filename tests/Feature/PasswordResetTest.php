<?php

namespace Tests\Feature;

use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Phase 13m: パスワードリセット (セルフサービス) テスト。
 */
class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // rate limit が他テストの影響を受けないように clear
        RateLimiter::clear('password-forgot:127.0.0.1');
    }

    private function makeUser(string $email = 'reset@example.com', string $role = User::ROLE_CUSTOMER): User
    {
        return User::create([
            'name' => 'Reset User',
            'email' => $email,
            'password' => Hash::make('old-password'),
            'role' => $role,
            'customer_id' => null,
        ]);
    }

    public function test_forgot_form_renders(): void
    {
        $response = $this->get('/password/forgot');
        $response->assertOk();
        $response->assertSee('パスワードリセット');
        $response->assertSee('メールアドレス');
    }

    public function test_existing_user_receives_reset_link(): void
    {
        $user = $this->makeUser();

        $response = $this->post('/password/forgot', ['email' => 'reset@example.com']);

        $response->assertRedirect(route('password.forgot'));
        $response->assertSessionHas('status');

        Mail::assertSent(PasswordResetMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email)
                && str_contains($mail->resetUrl, '/password/reset/')
                && str_contains($mail->resetUrl, urlencode($user->email));
        });

        // password_reset_tokens テーブルに行がある
        $row = DB::table('password_reset_tokens')->where('email', 'reset@example.com')->first();
        $this->assertNotNull($row);
    }

    public function test_unknown_email_returns_same_message_no_mail(): void
    {
        $response = $this->post('/password/forgot', ['email' => 'nonexistent@example.com']);

        // email enumeration 防止: 同じ status で返す
        $response->assertRedirect(route('password.forgot'));
        $response->assertSessionHas('status');

        Mail::assertNothingSent();
    }

    public function test_master_reset_url_uses_admin_domain(): void
    {
        $this->makeUser('master-reset@example.com', User::ROLE_MASTER);

        $this->post('/password/forgot', ['email' => 'master-reset@example.com']);

        Mail::assertSent(PasswordResetMail::class, function ($mail) {
            return str_contains($mail->resetUrl, 'admin.packto.jp');
        });
    }

    public function test_customer_reset_url_uses_app_domain(): void
    {
        $this->makeUser('customer-reset@example.com', User::ROLE_CUSTOMER);

        $this->post('/password/forgot', ['email' => 'customer-reset@example.com']);

        Mail::assertSent(PasswordResetMail::class, function ($mail) {
            return str_contains($mail->resetUrl, 'app.packto.jp');
        });
    }

    public function test_reset_form_renders_with_token(): void
    {
        $response = $this->get('/password/reset/dummy-token?email=foo@bar.com');
        $response->assertOk();
        $response->assertSee('新しいパスワードを設定');
        $response->assertSee('foo@bar.com');
    }

    public function test_valid_token_resets_password(): void
    {
        $user = $this->makeUser();
        $rawToken = 'valid-raw-token';
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make($rawToken),
            'created_at' => now(),
        ]);

        $response = $this->post('/password/reset', [
            'token' => $rawToken,
            'email' => $user->email,
            'password' => 'new-secure-pass',
            'password_confirmation' => 'new-secure-pass',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status');

        $user->refresh();
        $this->assertTrue(Hash::check('new-secure-pass', $user->password));

        // トークンは消費される
        $this->assertNull(DB::table('password_reset_tokens')->where('email', $user->email)->first());
    }

    public function test_invalid_token_rejected(): void
    {
        $user = $this->makeUser();
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('correct-token'),
            'created_at' => now(),
        ]);

        $response = $this->from('/password/reset/wrong-token')->post('/password/reset', [
            'token' => 'wrong-token',
            'email' => $user->email,
            'password' => 'new-pass-1234',
            'password_confirmation' => 'new-pass-1234',
        ]);

        $response->assertSessionHasErrors('email');
        $user->refresh();
        $this->assertTrue(Hash::check('old-password', $user->password));
    }

    public function test_expired_token_rejected(): void
    {
        $user = $this->makeUser();
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('valid-token'),
            'created_at' => now()->subMinutes(120),
        ]);

        $response = $this->from('/password/reset/valid-token')->post('/password/reset', [
            'token' => 'valid-token',
            'email' => $user->email,
            'password' => 'new-pass-1234',
            'password_confirmation' => 'new-pass-1234',
        ]);

        $response->assertSessionHasErrors('email');
        // 期限切れトークンは削除される
        $this->assertNull(DB::table('password_reset_tokens')->where('email', $user->email)->first());
    }

    public function test_rate_limit_blocks_after_5_requests(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $response = $this->post('/password/forgot', ['email' => 'foo@bar.com']);
            $response->assertRedirect(route('password.forgot'));
        }

        // 6 回目: lockout
        $response = $this->from('/password/forgot')->post('/password/forgot', [
            'email' => 'foo@bar.com',
        ]);
        $response->assertSessionHasErrors('email');
    }
}
