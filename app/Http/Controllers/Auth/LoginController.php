<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactorService;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    /** Phase 13k: 1 分間に同一 (email+IP) で 5 回まで。超えたら 60 秒 lockout */
    private const MAX_ATTEMPTS = 5;
    private const DECAY_SECONDS = 60;

    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $key = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "ログイン試行が多すぎます。{$seconds} 秒後に再試行してください。",
            ]);
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($key, self::DECAY_SECONDS);
            AuditLogger::record('auth.login_failed', [], ['email' => $credentials['email']]);
            throw ValidationException::withMessages([
                'email' => 'メールアドレスまたはパスワードが正しくありません。',
            ]);
        }

        RateLimiter::clear($key);

        $user = Auth::user();
        $user->forceFill(['last_login_at' => now()])->save();

        // Phase 13n: 2FA が有効なユーザはここで一旦ログアウトして challenge へ
        if ($user->hasTwoFactorEnabled()) {
            Auth::logout();
            $request->session()->put('2fa.pending_user_id', $user->id);
            $request->session()->put('2fa.remember', $request->boolean('remember'));
            return redirect()->route('two-factor.challenge');
        }

        $request->session()->regenerate();
        AuditLogger::record('auth.login',
            ['type' => 'user', 'id' => $user->id, 'label' => $user->email],
        );

        // ロールに応じて admin / app のダッシュボードへ
        return $user->isMaster()
            ? redirect()->intended(route('admin.dashboard'))
            : redirect()->intended(route('tenant.dashboard'));
    }

    /**
     * Phase 13n: 2FA challenge 画面 (パスワード認証後の 6 桁入力)
     */
    public function showTwoFactorChallenge(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('2fa.pending_user_id')) {
            return redirect()->route('login');
        }
        return view('auth.two_factor_challenge');
    }

    public function verifyTwoFactorChallenge(Request $request, TwoFactorService $tfa): RedirectResponse
    {
        $userId = $request->session()->get('2fa.pending_user_id');
        if (! $userId) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'code' => ['required', 'string'],
        ]);

        $user = User::findOrFail($userId);
        $code = trim($data['code']);
        $verified = false;
        $usedRecovery = false;

        // TOTP 6 桁
        if (preg_match('/^\d{6}$/', $code)) {
            $verified = $tfa->verify($user->two_factor_secret, $code);
        } else {
            // リカバリーコード
            $remaining = $tfa->consumeRecoveryCode($user->two_factor_recovery_codes ?? [], $code);
            if ($remaining !== null) {
                $user->forceFill(['two_factor_recovery_codes' => $remaining])->save();
                $verified = true;
                $usedRecovery = true;
            }
        }

        if (! $verified) {
            AuditLogger::record('auth.2fa_failed',
                ['type' => 'user', 'id' => $user->id, 'label' => $user->email],
            );
            throw ValidationException::withMessages([
                'code' => '2 段階認証コードが正しくありません。',
            ]);
        }

        $remember = (bool) $request->session()->pull('2fa.remember', false);
        $request->session()->forget('2fa.pending_user_id');
        $user->forceFill(['last_login_at' => now()])->save();
        Auth::login($user, $remember);
        $request->session()->regenerate();

        AuditLogger::record($usedRecovery ? 'auth.2fa_recovery_used' : 'auth.login',
            ['type' => 'user', 'id' => $user->id, 'label' => $user->email],
            $usedRecovery ? ['method' => 'recovery_code'] : ['method' => 'totp'],
        );

        return $user->isMaster()
            ? redirect()->intended(route('admin.dashboard'))
            : redirect()->intended(route('tenant.dashboard'));
    }

    /**
     * Throttle key: email を小文字化して IP と結合。
     *
     * Cloudflare proxied 環境では bootstrap/app.php の trustProxies 設定で
     * $request->ip() が CF-Connecting-IP 由来の実 IP になる。念のため
     * CF-Connecting-IP ヘッダを直接優先して二重防御。
     */
    private function throttleKey(Request $request): string
    {
        $ip = $request->header('CF-Connecting-IP') ?: $request->ip();
        return 'login:'.Str::lower((string) $request->input('email')).'|'.$ip;
    }

    public function logout(Request $request): RedirectResponse
    {
        $userId = Auth::id();
        $email = Auth::user()?->email;
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        AuditLogger::record('auth.logout',
            ['type' => 'user', 'id' => $userId, 'label' => $email],
        );

        return redirect()->route('login');
    }
}
