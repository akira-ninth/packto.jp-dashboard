<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
        $request->session()->regenerate();
        AuditLogger::record('auth.login',
            ['type' => 'user', 'id' => Auth::id(), 'label' => Auth::user()->email],
        );

        // ロールに応じて admin / app のダッシュボードへ
        return Auth::user()->isMaster()
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
