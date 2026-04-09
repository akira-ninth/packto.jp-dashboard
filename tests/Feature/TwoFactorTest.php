<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorTest extends TestCase
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

    public function test_two_factor_setup_page_renders(): void
    {
        $master = $this->makeMaster();
        $response = $this->actingAs($master)->get('/two-factor');
        $response->assertOk();
        $response->assertSee('2 段階認証');
    }

    public function test_setup_generates_secret_in_session(): void
    {
        $master = $this->makeMaster();

        $response = $this->actingAs($master)->post('/two-factor/setup');
        $response->assertRedirect(route('two-factor.show'));
        $this->assertNotNull(session('2fa.pending_secret'));

        // 後続の GET で QR が表示される
        $response2 = $this->actingAs($master)
            ->withSession(['2fa.pending_secret' => session('2fa.pending_secret')])
            ->get('/two-factor');
        $response2->assertSee('Authenticator');
    }

    public function test_confirm_with_valid_code_enables_2fa(): void
    {
        $master = $this->makeMaster();
        $tfa = $this->app->make(TwoFactorService::class);
        $secret = $tfa->generateSecret();
        $google2fa = new Google2FA();
        $code = $google2fa->getCurrentOtp($secret);

        $response = $this->actingAs($master)
            ->withSession(['2fa.pending_secret' => $secret])
            ->post('/two-factor/confirm', ['code' => $code]);

        $response->assertRedirect(route('two-factor.show'));
        $master->refresh();
        $this->assertTrue($master->hasTwoFactorEnabled());
        $this->assertEquals($secret, $master->two_factor_secret);
        $this->assertCount(6, $master->two_factor_recovery_codes);
    }

    public function test_confirm_with_wrong_code_rejected(): void
    {
        $master = $this->makeMaster();
        $secret = (new Google2FA())->generateSecretKey();

        $response = $this->actingAs($master)
            ->withSession(['2fa.pending_secret' => $secret])
            ->post('/two-factor/confirm', ['code' => '000000']);

        $response->assertSessionHasErrors('code');
        $master->refresh();
        $this->assertFalse($master->hasTwoFactorEnabled());
    }

    public function test_login_with_2fa_enabled_redirects_to_challenge(): void
    {
        $master = $this->makeMaster();
        $tfa = $this->app->make(TwoFactorService::class);
        $secret = $tfa->generateSecret();
        $master->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $tfa->generateRecoveryCodes(),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $response = $this->post('/login', [
            'email' => 'master@packto.jp',
            'password' => 'correct-pass',
        ]);

        $response->assertRedirect(route('two-factor.challenge'));
        $this->assertGuest(); // 2FA challenge 通過まではログイン未完
        $this->assertNotNull(session('2fa.pending_user_id'));
    }

    public function test_two_factor_challenge_with_valid_totp(): void
    {
        $master = $this->makeMaster();
        $tfa = $this->app->make(TwoFactorService::class);
        $secret = $tfa->generateSecret();
        $master->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $tfa->generateRecoveryCodes(),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $code = (new Google2FA())->getCurrentOtp($secret);

        $response = $this->withSession(['2fa.pending_user_id' => $master->id])
            ->post('/two-factor/challenge', ['code' => $code]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($master);
    }

    public function test_two_factor_challenge_with_recovery_code(): void
    {
        $master = $this->makeMaster();
        $tfa = $this->app->make(TwoFactorService::class);
        $secret = $tfa->generateSecret();
        $codes = $tfa->generateRecoveryCodes();
        $master->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $codes,
            'two_factor_confirmed_at' => now(),
        ])->save();

        $response = $this->withSession(['2fa.pending_user_id' => $master->id])
            ->post('/two-factor/challenge', ['code' => $codes[0]]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($master);

        // リカバリーコードは消費される
        $master->refresh();
        $this->assertCount(5, $master->two_factor_recovery_codes);
        $this->assertNotContains($codes[0], $master->two_factor_recovery_codes);
    }

    public function test_two_factor_challenge_with_wrong_code_rejected(): void
    {
        $master = $this->makeMaster();
        $tfa = $this->app->make(TwoFactorService::class);
        $secret = $tfa->generateSecret();
        $master->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $tfa->generateRecoveryCodes(),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $response = $this->withSession(['2fa.pending_user_id' => $master->id])
            ->from(route('two-factor.challenge'))
            ->post('/two-factor/challenge', ['code' => '000000']);

        $response->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_disable_with_correct_password(): void
    {
        $master = $this->makeMaster();
        $tfa = $this->app->make(TwoFactorService::class);
        $secret = $tfa->generateSecret();
        $master->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $tfa->generateRecoveryCodes(),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $response = $this->actingAs($master)
            ->post('/two-factor/disable', ['current_password' => 'correct-pass']);

        $response->assertRedirect();
        $master->refresh();
        $this->assertFalse($master->hasTwoFactorEnabled());
        $this->assertNull($master->two_factor_secret);
    }

    public function test_disable_with_wrong_password_rejected(): void
    {
        $master = $this->makeMaster();
        $tfa = $this->app->make(TwoFactorService::class);
        $secret = $tfa->generateSecret();
        $master->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $tfa->generateRecoveryCodes(),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $response = $this->actingAs($master)
            ->from(route('two-factor.show'))
            ->post('/two-factor/disable', ['current_password' => 'wrong-pass']);

        $response->assertSessionHasErrors('current_password');
        $master->refresh();
        $this->assertTrue($master->hasTwoFactorEnabled());
    }
}
