<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
            throw ValidationException::withMessages([
                'email' => 'メールアドレスまたはパスワードが正しくありません。',
            ]);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        // ロールに応じて admin / app のダッシュボードへ
        return Auth::user()->isMaster()
            ? redirect()->intended(route('admin.dashboard'))
            : redirect()->intended(route('tenant.dashboard'));
    }

    /**
     * Throttle key: email を小文字化して IP と結合 (email enumeration を避けるため
     * password 試行は email + IP のペア、両方ともこのコントローラに到達するときだけ hit)
     */
    private function throttleKey(Request $request): string
    {
        return 'login:'.Str::lower((string) $request->input('email')).'|'.$request->ip();
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
